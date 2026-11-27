<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Category,Product,PreorderSchedule,OrderItem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProductImageService;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
class CrudController extends Controller {
    private function model(string $type): string {
        return match($type){'categories'=>Category::class,'products'=>Product::class,'schedules'=>PreorderSchedule::class,default=>abort(404)};
    }
    private function rules(string $type,?int $id=null): array {
        return match($type){
            'categories'=>['name'=>['required','string','max:100',Rule::unique('categories')->ignore($id)],'description'=>'nullable|string'],
            'products'=>['category_id'=>'required|exists:categories,id','name'=>'required|string|max:150','description'=>'nullable|string',
                'price'=>'required|numeric|min:0|max:9999999999.99|decimal:0,2','stock'=>'required|integer|min:0|max:1000000',
                'image'=>'prohibited',
                'image_file'=>'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=4096,max_height=4096',
                'is_available'=>'required|boolean'],
            'schedules'=>['title'=>'required|string|max:150','start_at'=>'required|date','end_at'=>'required|date|after:start_at',
                'fulfillment_at'=>'nullable|date|after_or_equal:end_at','quota'=>'required|integer|min:1|max:1000000','status'=>'required|in:draft,open,closed'],
        };
    }
    public function index(string $type){return $this->model($type)::orderBy('id')->get();}
    private function prepareData(Request $r, string $type, ?int $id = null): array {
        $data = $r->validate($this->rules($type, $id));
        $file = $data['image_file'] ?? null;
        unset($data['image_file'], $data['image']);
        $stored = null;
        if ($type === 'products' && $file) {
            $stored = app(ProductImageService::class)->store($file);
            $data['image'] = $stored;
        }
        return [$data, $stored];
    }

    public function store(Request $r, string $type) {
        $model = $this->model($type);
        [$data, $stored] = $this->prepareData($r, $type);
        try {
            $row = $model::create($data);
        } catch (\Throwable $e) {
            if ($stored) app(ProductImageService::class)->discard($stored);
            throw $e;
        }
        return response()->json($row, 201);
    }

    public function update(Request $r, string $type, int $id) {
        $model = $this->model($type);
        [$data, $stored] = $this->prepareData($r, $type, $id);
        try {
            [$row,$old] = DB::transaction(function () use ($model, $data, $type, $id) {
                $row = $model::lockForUpdate()->findOrFail($id);
                if ($type === 'schedules') {
                    $used = OrderItem::whereHas('order', fn($q) => $q->where('schedule_id', $id)->where('status', '!=', 'cancelled'))->sum('quantity');
                    abort_if($data['quota'] < $used, 422, 'Kuota di bawah porsi yang sudah dipesan.');
                }
                $old = $type === 'products' ? $row->image : null;
                $row->update($data);
                return [$row,$old];
            });
        } catch (\Throwable $e) {
            if ($stored) app(ProductImageService::class)->discard($stored);
            throw $e;
        }
        if ($stored && $old !== $stored) app(ProductImageService::class)->cleanup($old);
        return $row;
    }

    public function destroy(string $type,int $id){
        try {
            $old=DB::transaction(function() use ($type,$id) {
                $row=$this->model($type)::lockForUpdate()->findOrFail($id);
                if ($type==='categories' && Product::withTrashed()->where('category_id',$id)->exists()) abort(409,'Kategori masih terkait dengan menu atau riwayat transaksi.');
                if ($type!=='products') { $row->delete(); return null; }
                $old=$row->image;
                if (OrderItem::where('product_id',$id)->exists()) {
                    // Hide archived products but retain FK, name/price snapshots and stock restoration.
                    $row->update(['is_available'=>false,'image'=>null]);
                    $row->delete();
                } else { $row->forceDelete(); }
                return $old;
            });
        } catch(QueryException $e){abort(409,'Data masih dipakai atau tidak dapat dihapus.');}
        app(ProductImageService::class)->cleanup($old);
        return response()->noContent();
    }
}
