import '../css/meal-order.css';
const app=document.querySelector('#meal-app');
if(app) init();
function init(){
    const $=id=>document.getElementById(id), role=app.dataset.role, admin=role==='admin';
    const money=n=>new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0,maximumFractionDigits:2}).format(Number(n)||0);
    const el=(tag,text='',cls='')=>{const e=document.createElement(tag);e.textContent=text;e.className=cls;return e;};
    const node=(tag,cls,...children)=>{const e=el(tag,'',cls);e.append(...children);return e;};
    const msg=(text,error=false)=>{const e=$('message');e.textContent=text;e.hidden=!text;e.classList.toggle('error',error);if(error)e.scrollIntoView({block:'nearest'});};
    const run=async fn=>{try{await fn();}catch(e){msg(e.message||'Terjadi kesalahan.',true);}};
    const btn=(t,fn,cls='')=>{const b=el('button',t,'mo-btn '+cls);b.type='button';b.onclick=async()=>{if(b.disabled)return;b.disabled=true;try{await run(fn);}finally{if(b.isConnected)b.disabled=false;}};return b;};
    async function api(path,method='GET',data){
        // JSON untuk transaksi biasa, multipart untuk upload foto.
        const multipart=data instanceof FormData;
        const headers={Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content};
        if(!multipart)headers['Content-Type']='application/json';
        const response=await fetch('/api'+path,{method,credentials:'same-origin',headers,...(data!==undefined?{body:multipart?data:JSON.stringify(data)}:{})});
        let body=null; if(response.status!==204){try{body=await response.json();}catch{}}
        if(!response.ok){
            if(response.status===401)throw Error('Sesi berakhir. Masuk kembali untuk melanjutkan.');
            if(response.status===419)throw Error('Sesi keamanan berakhir. Segarkan halaman dan coba lagi.');
            if(response.status===403)throw Error('Akun ini tidak memiliki akses untuk tindakan tersebut.');
            const details=body?.errors?Object.values(body.errors).flat().join(' '):'';throw Error(details||body?.message||`Permintaan gagal (HTTP ${response.status}).`);}
        return body;
    }
    let initialized=false;
    const titles={dashboard:'Ringkasan',pesanan:'Pesanan masuk',menu:'Kelola menu',kategori:'Kategori',jadwal:'Jadwal pre-order',laporan:'Laporan',pelanggan:'Pelanggan'};
    function page(){
        const pages=[...app.querySelectorAll('[data-page]')],hash=location.hash.slice(1);
        const id=hash==='menu-list'&&!admin?'menu':pages.some(p=>p.dataset.page===hash)?hash:(admin?'dashboard':'menu');
        pages.forEach(p=>p.hidden=p.dataset.page!==id);
        app.querySelectorAll('[data-nav]').forEach(a=>{const active=a.dataset.nav===id;a.classList.toggle('active',active);if(active)a.setAttribute('aria-current','page');else a.removeAttribute('aria-current');});
        if(admin){
            $('page-title').textContent=titles[id];
            $('admin-nav').classList.remove('is-open');
            $('admin-toggle').setAttribute('aria-expanded','false');
        }
        if(!admin&&$('cart-bar'))$('cart-bar').hidden=id!=='menu'||countCart()===0;
        if(hash==='menu-list'&&!admin)requestAnimationFrame(()=>$('menu-list').scrollIntoView({behavior:'smooth'}));else window.scrollTo(0,0);
        if(id==='pesanan'&&hash==='pesanan'&&initialized)run(loadOrders);
    }
    const states={pending:'Menunggu konfirmasi',confirmed:'Pesanan diterima',processing:'Sedang dimasak',ready:'Siap diambil',completed:'Selesai',cancelled:'Dibatalkan'};
    const pays={unpaid:'Belum dibayar',waiting_confirmation:'Menunggu konfirmasi',paid:'Lunas',rejected:'Pembayaran ditolak'};
    const next={pending:['confirmed','Terima pesanan'],confirmed:['processing','Mulai memasak'],processing:['ready','Tandai siap diambil'],ready:['completed','Selesaikan pesanan']};
    const empty=t=>node('div','mo-empty',el('span','○','mo-empty-icon'),el('p',t));
    const pill=(v,map)=>el('span',map[v]||v||'—','mo-pill '+(v||''));
    let customers=[],deliveryFee=0,allOrders=[],report={completed_orders:0,sales:0},filter='all',dataSets={categories:[],products:[],schedules:[]};
    function orderCard(o,actions=true){
        const items=o.items||[],pay=o.payment?.status||'unpaid';
        const info=node('div','',el('h3',o.user?.name||(admin?`Pelanggan #${o.user_id}`:'Pesanan kamu')),el('p',o.order_code,'mo-code'));
        if(o.created_at){const date=new Date(o.created_at);if(!Number.isNaN(date.getTime()))info.append(el('p',date.toLocaleString('id-ID'),'mo-code'));}
        const box=node('article','mo-order',node('div','mo-order-head',info,pill(o.status,states)));
        const list=node('div','mo-items');
        items.forEach(i=>list.append(node('div','mo-item',node('div','mo-order-product',imageThumb({image:i.product_image,name:i.product_name}),el('span',`${i.product_name} × ${i.quantity}`)),el('strong',money(i.subtotal??Number(i.price)*i.quantity)))));
        box.append(list);
        box.append(el('p',(o.fulfillment==='delivery'?'Delivery':'Pickup')+' · '+(o.schedule?.title||'Jadwal PO')+(o.schedule?.fulfillment_at?' · '+new Date(o.schedule.fulfillment_at).toLocaleString('id-ID'):''),'mo-note'));
        if(o.fulfillment==='delivery')box.append(el('p',`${o.delivery_address||''} · ${o.contact_phone||''} · Ongkir ${money(o.delivery_fee||0)}`,'mo-note'));
        if(o.cancellation_reason)box.append(el('p','Alasan batal: '+o.cancellation_reason,'mo-note'));
        if(o.history?.length)box.append(node('details','mo-history',el('summary','Riwayat perubahan'),...o.history.map(h=>el('p',`${states[h.status]||h.status} · ${new Date(h.created_at).toLocaleString('id-ID')}`,'mo-code'))));
        if(o.notes)box.append(el('p','Catatan: '+o.notes,'mo-note'));
        box.append(node('div','mo-item',el('span','Total pesanan'),el('strong',money(o.total_amount))),node('div','mo-item',el('span','Pembayaran '+({cash:'tunai',transfer:'transfer',manual:'manual'}[o.payment?.method]||'manual')),pill(pay,pays)));
        if(!admin&&o.status!=='cancelled'){
            const steps=['pending','confirmed','processing','ready','completed'];
            const labels=['Dipesan','Diterima','Dimasak','Siap','Selesai'];
            box.append(node('div','mo-tracking',...steps.map((s,i)=>node('div','mo-track-step',el('div','','mo-track '+(i<=steps.indexOf(o.status)?'on':'')),el('small',labels[i])))));
        }
        if(admin&&actions&&!['completed','cancelled'].includes(o.status)){
            const controls=node('div','mo-actions');
            const action=next[o.status];
            if(action){const b=btn(action[1],async()=>{if(action[0]==='completed'&&pay!=='paid')throw Error('Konfirmasi pembayaran terlebih dahulu.');await api(`/admin/orders/${o.id}/status`,'PATCH',{status:action[0]});await loadOrders();msg('Status pesanan diperbarui.');});if(action[0]==='completed'&&pay!=='paid'){b.disabled=true;b.title='Konfirmasi pembayaran terlebih dahulu.';}controls.append(b);}
            if(['pending','confirmed'].includes(o.status))controls.append(btn('Batalkan',async()=>{const reason=prompt('Alasan pembatalan (stok akan dikembalikan):');if(!reason?.trim())return;await api(`/admin/orders/${o.id}/status`,'PATCH',{status:'cancelled',reason:reason.trim()});await refreshAdmin();msg('Pesanan dibatalkan.');},'mo-danger'));
            if(pay!=='paid')controls.append(btn('Konfirmasi pembayaran',async()=>{if(!confirm('Pastikan pembayaran manual sudah diterima. Tandai lunas?'))return;await api(`/admin/orders/${o.id}/payment`,'PATCH',{status:'paid'});await loadOrders();msg('Pembayaran dikonfirmasi.');},'mo-outline'));
            const sel=document.createElement('select');sel.className='mo-chip';sel.setAttribute('aria-label','Status pembayaran');for(const [v,label] of Object.entries(pays)){const op=el('option',label);op.value=v;sel.append(op);}sel.value=pay;
            const advanced=node('details','mo-payment-more',el('summary','Opsi pembayaran lainnya'),node('div','mo-actions',sel,btn('Simpan status pembayaran',async()=>{if(sel.value==='paid'&&!confirm('Tandai pembayaran sudah diterima?'))return;await api(`/admin/orders/${o.id}/payment`,'PATCH',{status:sel.value});await loadOrders();msg('Status pembayaran disimpan.');},'mo-outline')));controls.append(advanced);
            box.append(controls);
        }
        return box;
    }
    function renderReport(r,chartId='report-chart'){
        const chart=$(chartId);if(chart){const entries=Object.entries(r.daily||{}),max=Math.max(1,...entries.map(([k,v])=>Number(v)));chart.replaceChildren(...(entries.length?entries.map(([k,v])=>{const bar=node('div','mo-bar');bar.style.width=`${Number(v)/max*100}%`;return node('div','mo-chart-row',el('span',k),node('div','mo-bar-track',bar),el('strong',money(v)));}):[empty('Belum ada transaksi selesai.') ]));}
        if(chartId==='report-chart'){
          $('report').replaceChildren(metric('Pesanan selesai',r.completed_orders),metric('Pendapatan',money(r.sales)));
          $('report-transactions').replaceChildren(...((r.transactions||[]).length?r.transactions.map(o=>node('div','mo-list-row',node('div','mo-list-info',el('strong',o.order_code),el('small',new Date(o.updated_at).toLocaleString('id-ID')+' · '+o.fulfillment)),el('strong',money(o.total_amount)))):[empty('Tidak ada transaksi pada periode ini.')]));
        }
    }
    function renderCustomers(){const search=$('customer-search').value.trim().toLowerCase();const rows=customers.filter(c=>[c.name,c.email,c.phone].some(x=>String(x||'').toLowerCase().includes(search)));$('customer-list').replaceChildren(...(rows.length?rows.map(c=>node('div','mo-list-row',node('div','mo-list-info',el('strong',c.name),el('small',c.email+' · '+(c.phone||'Telepon belum diisi'))),el('span',c.orders_count+' pesanan'))):[empty('Tidak ada pelanggan yang cocok.') ]));}
    async function filteredReport(){const q=new URLSearchParams();if($('report-from').value)q.set('from',$('report-from').value);if($('report-to').value)q.set('to',$('report-to').value);renderReport(await api('/admin/report?'+q));$('report-export').href='/api/admin/report/export?'+q;}
    function metric(label,value){return node('div','mo-metric',el('small',label),el('strong',String(value)));}
    const activeSchedules=()=>dataSets.schedules.filter(s=>s.status==='open'&&new Date(s.start_at)<=new Date()&&new Date(s.end_at)>new Date()&&(!s.fulfillment_at||new Date(s.fulfillment_at)>new Date()));
    const remainingQuota=()=>activeSchedules().reduce((n,s)=>n+Math.max(0,Number(s.quota)-allOrders.filter(o=>String(o.schedule_id)===String(s.id)&&o.status!=='cancelled').reduce((sum,o)=>sum+(o.items||[]).reduce((a,i)=>a+Number(i.quantity),0),0)),0);
    function renderOrders(){
        if(!$('orders'))return;
        const search=admin?$('order-search').value.trim().toLowerCase():'';
        const visible=allOrders.filter(o=>(!admin||filter==='all'||o.status===filter)&&(!admin||!$('order-date').value||new Date(o.created_at).toLocaleDateString('sv-SE')===$('order-date').value)&&(!search||[o.order_code,o.user?.name].some(v=>String(v||'').toLowerCase().includes(search))));
        $('orders').replaceChildren(...(visible.length?visible.map(o=>orderCard(o)):[empty('Belum ada pesanan pada bagian ini.')]));
        if(admin){
            $('pending-count').textContent=allOrders.filter(o=>o.status==='pending').length;
            $('order-filters').replaceChildren(...[['all','Semua'],...Object.entries(states)].map(([v,t])=>{const b=el('button',t,'mo-chip '+(filter===v?'active':''));b.type='button';b.setAttribute('aria-pressed',String(filter===v));b.onclick=()=>{filter=v;renderOrders();};return b;}));
            $('recent-orders').replaceChildren(...(allOrders.length?allOrders.slice(0,3).map(o=>orderCard(o,false)):[empty('Belum ada pesanan masuk.')]));
            $('metrics').replaceChildren(metric('Total pesanan',allOrders.length),metric('Pelanggan',report.customers??customers.length),metric('Pesanan selesai',report.completed_orders),metric('Pesanan baru',allOrders.filter(o=>o.status==='pending').length),metric('Sedang dimasak',allOrders.filter(o=>o.status==='processing').length),metric('Menu terdaftar',dataSets.products.length),metric('Stok tersedia',dataSets.products.filter(p=>p.is_available).reduce((n,p)=>n+Number(p.stock),0)),metric('Jadwal PO aktif',activeSchedules().length),metric('Sisa kuota PO',remainingQuota()),metric('Penjualan selesai',money(report.sales)));
            renderReport(report,'dashboard-chart');
        }
    }
    async function loadOrders(){if(!$('orders'))return;allOrders=await api(admin?'/admin/orders':'/orders');if(admin){report=await api('/admin/report');await filteredReport(); }renderOrders();}
    let cart={},products=[],schedules=[],category='all';
    try{const raw=JSON.parse(localStorage.getItem('meal-cart')||'{}');if(raw&&typeof raw==='object'&&!Array.isArray(raw))cart=raw;}catch{}
    const qty=p=>Math.max(0,Math.min(100,Number(p.stock)||0,Math.floor(Number(cart[p.id])||0)));
    const chosen=()=>products.filter(p=>qty(p)>0);
    const countCart=()=>chosen().reduce((n,p)=>n+qty(p),0);
    const totalCart=()=>chosen().reduce((n,p)=>n+Math.round(Number(p.price)*100)*qty(p),0)/100;
    const fee=()=> $('fulfillment')?.value==='delivery'&&countCart()?deliveryFee:0;
    function renderTotals(){if(!$('checkout-totals'))return;$('delivery-fields').hidden=$('fulfillment').value!=='delivery';$('checkout-totals').replaceChildren(node('div','mo-item',el('span','Subtotal'),el('strong',money(totalCart()))),node('div','mo-item',el('span','Ongkos kirim'),el('strong',money(fee()))),node('div','mo-total',el('span','Total estimasi'),el('strong',money(totalCart()+fee()))));}

    function setQty(p,n){if(n>0&&!qty(p)&&chosen().length>=50){msg('Maksimal 50 jenis menu dalam satu pesanan.',true);return;}cart[p.id]=Math.max(0,Math.min(n,p.stock,100));if(!cart[p.id])delete cart[p.id];persist();for(const c of $('catalog').querySelectorAll('meal-card'))if(c.productId===p.id)c.quantity=qty(p);}
    function persist(){try{localStorage.setItem('meal-cart',JSON.stringify(cart));}catch{}renderCart();}
    function renderCart(){
        const target=$('cart');if(!target)return;
        target.replaceChildren();
        for(const p of chosen()){const q=qty(p);const plus=btn('+',()=>setQty(p,q+1),'mo-outline');plus.disabled=q>=Math.min(p.stock,100);target.append(node('div','mo-cart-row',imageThumb(p),node('div','mo-list-info',el('strong',p.name),el('small',money(p.price)+' / porsi')),node('div','mo-stepper',btn('−',()=>setQty(p,q-1),'mo-outline'),el('strong',String(q)),plus),el('strong',money(Number(p.price)*q)),btn('Hapus',()=>setQty(p,0),'mo-danger')));}
        if(!countCart())target.append(empty('Keranjang masih kosong. Pilih menu terlebih dahulu.'));
        target.append(node('div','mo-total',el('span','Estimasi total'),el('strong',money(totalCart()))));
        $('checkout').disabled=!countCart()||!$('schedule').value;
        $('quote').disabled=!countCart();renderTotals();
        const preview=$('basket-preview-items');
        if(preview){
            preview.replaceChildren(...(chosen().length?chosen().map(p=>node('div','mo-basket-row',imageThumb(p),node('div','mo-list-info',el('strong',p.name),el('small',`${qty(p)} × ${money(p.price)}`)),el('strong',money(qty(p)*Number(p.price))))):[empty('Belum ada menu. Tambahkan makanan favoritmu.')]));
            $('basket-preview-total').textContent=money(totalCart());
        }
        $('cart-count').textContent=countCart();$('cart-summary').textContent=`${countCart()} porsi · ${money(totalCart())}`;$('cart-bar').hidden=!countCart()||!app.querySelector('[data-page="menu"]:not([hidden])');
    }
    function renderCatalog(){
        const text=$('search').value.trim().toLowerCase(),visible=products.filter(p=>(category==='all'||String(p.category_id)===category)&&[p.name,p.description,p.category?.name].some(x=>String(x||'').toLowerCase().includes(text)));
        $('catalog-count').textContent=`${visible.length} menu ditampilkan`;
        $('catalog').replaceChildren(...(visible.length?visible.map(p=>{const c=document.createElement('meal-card');c.product=p;c.quantity=qty(p);c.addEventListener('detail-meal',()=>{const d=$('detail-content');d.replaceChildren(imageThumb(p),el('h2',p.name),el('p',p.description||'Tidak ada deskripsi.'),el('strong',money(p.price)),el('p',`Stok ${p.stock} porsi`),btn('Tambah ke keranjang',()=>{if(qty(p)>=Math.min(p.stock,100))throw Error('Stok tidak mencukupi.');setQty(p,qty(p)+1);$('food-detail').close();}));$('food-detail').showModal();});c.addEventListener('add-meal',()=>{if(qty(p)>=Math.min(p.stock,100))return;setQty(p,qty(p)+1);c.quantity=qty(p);msg(p.name+' ditambahkan ke keranjang.');});return c;}):[empty('Tidak ada menu yang cocok.')]));
        const cats=[...new Map(products.map(p=>[String(p.category_id),p.category?.name||'Menu'])).entries()];
        $('categories').replaceChildren(...[['all','Semua menu'],...cats].map(([v,t])=>{const b=el('button',t,'mo-chip '+(category===v?'active':''));b.type='button';b.setAttribute('aria-pressed',String(category===v));b.onclick=()=>{category=v;renderCatalog();};return b;}));
    }
    async function catalog(){
        let prev=$('schedule').value;try{if(!prev)prev=sessionStorage.getItem('meal-schedule')||'';}catch{}[products,schedules]=await Promise.all([api('/catalog'),api('/schedules')]);const settings=await api('/settings');deliveryFee=Number(settings.delivery_fee)||0;const delivery=$('fulfillment').querySelector('[value=delivery]');if(delivery){delivery.disabled=!settings.delivery_enabled;delivery.hidden=!settings.delivery_enabled;}if(!settings.delivery_enabled)$('fulfillment').value='pickup';
        const date=d=>new Date(d).toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'short'});
        const options=schedules.map(s=>{const o=el('option',s.title+' · sampai '+date(s.end_at));o.value=s.id;return o;});
        if(!options.length){const o=el('option','Belum ada jadwal PO aktif');o.value='';options.push(o);}
        $('schedule').replaceChildren(...options);if(schedules.some(s=>String(s.id)===prev))$('schedule').value=prev;
        $('schedule-hint').textContent=schedules.length?`${schedules.length} jadwal pre-order sedang dibuka`:'Pre-order belum dibuka. Kamu masih bisa melihat menu.';
        updateSchedule();
        for(const k of Object.keys(cart)){const p=products.find(x=>String(x.id)===k);if(!p||!qty(p))delete cart[k];else cart[k]=qty(p);}
        renderCatalog();persist();
    }
    function updateSchedule(){
        const s=schedules.find(x=>String(x.id)===$('schedule').value);
        $('schedule-detail').textContent=s?'PO ditutup '+new Date(s.end_at).toLocaleString('id-ID')+' · kuota awal '+s.quota+' porsi.'+(s.fulfillment_at?' · Pengambilan/pengiriman '+new Date(s.fulfillment_at).toLocaleString('id-ID'):' · Waktu pengambilan dikonfirmasi penjual.'):'Belum ada jadwal aktif. Coba kembali nanti.';
        $('checkout').disabled=!s||!countCart();
    }
    const configs={
      categories:{title:'Kategori makanan',singular:'Kategori',fields:[['name','Nama kategori','text',true],['description','Deskripsi','textarea']]},
      products:{title:'Kelola menu',singular:'Menu',fields:[['category_id','Kategori','category',true],['name','Nama makanan','text',true],['description','Deskripsi makanan','textarea'],['price','Harga (Rp)','number',true],['stock','Stok (porsi)','number',true],['is_available','Tampilkan di katalog','checkbox']]},
      schedules:{title:'Jadwal pre-order',singular:'Jadwal',fields:[['title','Nama jadwal','text',true],['start_at','Mulai PO','datetime-local',true],['end_at','Tutup PO','datetime-local',true],['fulfillment_at','Waktu pengambilan/pengiriman','datetime-local'],['quota','Kuota porsi','number',true],['status','Status jadwal','status',true]]}
    };
    function localPhoto(path){
        const p=String(path||'').replace(/^\/?storage\//,'').replace(/^\//,'');
        if(!/^(?:meal-images|images)\/[a-zA-Z0-9_./-]+$/.test(p)||p.includes('..'))return '/images/meal-placeholder.svg';
        return p.startsWith('meal-images/')?'/storage/'+p:'/'+p;
    }
    function imageThumb(p){const e=document.createElement('img');e.className='mo-thumb';e.src=localPhoto(p.image);e.alt=p.name||'';e.loading='lazy';e.onerror=()=>{e.onerror=null;e.src='/images/meal-placeholder.svg';};return e;}
    function renderAdmin(type){
        const cfg=configs[type],target=$('admin-'+type),rows=dataSets[type];let editing=null;const inputs={};
        const form=node('form','mo-form');form.hidden=true;
        const heading=el('h3','Tambah '+cfg.singular),grid=node('div','mo-form-grid');form.append(heading,grid);
        for(const [key,label,kind,required] of cfg.fields){
            let input;
            if(kind==='category'||kind==='status'){
                input=document.createElement('select');const opts=kind==='category'?[['','Pilih kategori'],...dataSets.categories.map(c=>[c.id,c.name])]:[['draft','Draf'],['open','Dibuka'],['closed','Ditutup']];
                opts.forEach(([v,t])=>{const o=el('option',t);o.value=v;input.append(o);});
            }else if(kind==='textarea'){input=document.createElement('textarea');}
            else{input=document.createElement('input');input.type=kind;if(kind==='number'){input.min=key==='quota'?1:0;input.step=key==='price'?'0.01':'1';}}
            if(required)input.required=true;inputs[key]=input;grid.append(node('label','mo-field'+(kind==='checkbox'?' mo-check-field':''),el('span',label),input));
        }
        // Formulir foto hanya ada di Kelola menu, bukan kategori/jadwal.
        let photoInput=null,photoPreview=null,previewUrl=null,photoHelp=null,oldPhoto=null;
        function releasePreview(){if(previewUrl){URL.revokeObjectURL(previewUrl);previewUrl=null;}}
        function showPhoto(){
            if(!photoPreview)return;
            releasePreview();
            const file=photoInput.files[0];
            photoPreview.hidden=true;photoPreview.removeAttribute('src');
            if(file){
                if(!['image/jpeg','image/png','image/webp'].includes(file.type)||!(/\.(?:jpe?g|png|webp)$/i.test(file.name))||file.size>5*1024*1024){
                    photoHelp.textContent='File tidak valid. Ganti foto atau batalkan pilihan sebelum menyimpan.';return;
                }
                previewUrl=URL.createObjectURL(file);photoPreview.src=previewUrl;photoPreview.hidden=false;
                photoHelp.textContent='Foto baru: '+file.name;
            }else{
                photoHelp.textContent='JPG, PNG atau WEBP · Maks. 5 MB · Maks. 12 megapiksel.';
                photoPreview.src=localPhoto(oldPhoto);photoPreview.hidden=false;
            }
        }
        if(type==='products'){
            photoInput=document.createElement('input');photoInput.type='file';photoInput.accept='.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp';
            photoPreview=document.createElement('img');photoPreview.className='mo-upload-preview';photoPreview.alt='Pratinjau foto';photoPreview.hidden=true;photoPreview.onerror=()=>{if(!photoPreview.src.endsWith('/images/meal-placeholder.svg'))photoPreview.src='/images/meal-placeholder.svg';else photoPreview.hidden=true;};
            photoHelp=el('small','','mo-muted');
            const upload=node('div','mo-upload',node('div','mo-upload-copy',el('strong','Foto makanan'),el('p','Pilih foto dari perangkat. Foto lama tetap dipakai jika tidak diganti.'),node('label','mo-field',el('span','Pilih file gambar'),photoInput),photoHelp,btn('Batalkan pilihan foto baru',()=>{photoInput.value='';showPhoto();},'mo-outline')),photoPreview);
            form.insertBefore(upload,grid);
            photoInput.onchange=showPhoto;
        }
        function open(row=null){editing=row?.id??null;heading.textContent=(row?'Edit ':'Tambah ')+cfg.singular;form.reset();
            for(const [key,input] of Object.entries(inputs)){if(input.type==='checkbox')input.checked=row?Boolean(row[key]):true;else if(input.type==='datetime-local'&&row?.[key]){const d=new Date(row[key]);input.value=Number.isNaN(d.getTime())?'':new Date(d.getTime()-d.getTimezoneOffset()*60000).toISOString().slice(0,16);}else if(row)input.value=row[key]??'';}
            if(photoInput){oldPhoto=row?.image??null;showPhoto();}
            if(type==='products'&&!dataSets.categories.length){msg('Buat kategori terlebih dahulu melalui menu Kategori.',true);location.hash='kategori';return;}
            form.hidden=false;list.hidden=true;search.closest('label').hidden=true;form.scrollIntoView({block:'nearest'});Object.values(inputs)[0]?.focus();
        }
        const submit=el('button','Simpan','mo-btn');submit.type='submit';form.append(node('div','mo-actions',submit,btn('Batal',()=>{releasePreview();form.hidden=true;list.hidden=false;search.closest('label').hidden=false;},'mo-outline')));
        form.onsubmit=async e=>{e.preventDefault();submit.disabled=true;try{await run(async()=>{
            const payload=Object.fromEntries(Object.entries(inputs).map(([key,input])=>[key,input.type==='checkbox'?input.checked:input.type==='datetime-local'&&input.value?new Date(input.value).toISOString():input.value]));
            const path='/admin/'+type+(editing?'/'+editing:'');
            if(photoInput?.files[0]){
                const file=photoInput.files[0];
                if(file.size>5*1024*1024||!['image/jpeg','image/png','image/webp'].includes(file.type)||!(/\.(?:jpe?g|png|webp)$/i.test(file.name)))throw Error('Foto harus JPG, PNG atau WEBP, maksimal 5 MB.');
                const multipart=new FormData();
                for(const [key,value] of Object.entries(payload))multipart.append(key,typeof value==='boolean'?(value?'1':'0'):value);
                multipart.append('image_file',file);
                // PHP membaca file multipart melalui POST; Laravel mengenali _method=PUT.
                if(editing)multipart.append('_method','PUT');
                await api(path,'POST',multipart);
            }else await api(path,editing?'PUT':'POST',payload);
            releasePreview();await refreshAdmin();msg('Data berhasil disimpan.');});}finally{submit.disabled=false;}};
        const search=document.createElement('input');search.type='search';search.placeholder='Cari '+cfg.singular.toLowerCase()+'...';search.setAttribute('aria-label','Cari '+cfg.singular);
        const list=node('div','mo-admin-list');
        function showRows(){
            const visible=rows.filter(r=>String(r.name||r.title).toLowerCase().includes(search.value.toLowerCase()));
            list.replaceChildren(...(visible.length?visible.map(r=>{
                let desc=type==='products'?`${dataSets.categories.find(c=>c.id===r.category_id)?.name||'Kategori'} · ${money(r.price)} · Stok ${r.stock} · ${r.is_available?'Tersedia':'Disembunyikan'}`:type==='schedules'?`${({open:'Dibuka',draft:'Draf',closed:'Ditutup'})[r.status]} · Kuota ${r.quota} porsi · ${new Date(r.start_at).toLocaleString('id-ID')} – ${new Date(r.end_at).toLocaleString('id-ID')}`:r.description||'Tanpa deskripsi';
                const children=[];if(type==='products')children.push(imageThumb(r));children.push(node('div','mo-list-info',el('strong',r.name||r.title),el('small',desc)));
                children.push(node('div','mo-actions',btn('Edit',()=>open(r),'mo-outline'),btn('Hapus',async()=>{if(!confirm(`Hapus ${r.name||r.title}? ${type==='products'?'Menu yang pernah dipesan akan diarsipkan. Riwayat transaksi tetap tersimpan.':''}`))return;await api(`/admin/${type}/${r.id}`,'DELETE');await refreshAdmin();msg(type==='products'?'Menu dihapus dari katalog. Riwayat transaksi tetap aman.':'Data dihapus.');},'mo-danger')));
                return node('div','mo-list-row',...children);
            }):[empty('Belum ada data yang cocok.')]));
        }
        search.oninput=showRows;showRows();
        target.replaceChildren(node('div','mo-panel',node('div','mo-section-head',node('div','',el('h2',cfg.title),el('p',type==='products'?'Atur foto, harga, stok, dan ketersediaan menu.':type==='categories'?'Kelompokkan makanan agar mudah ditemukan.':'Tentukan waktu dan kuota pre-order.')),btn('+ Tambah '+cfg.singular,()=>open())),node('label','mo-search',el('span','⌕'),search),form,list));
    }
    async function refreshAdmin(){const [categories,products,schedules]=await Promise.all(['categories','products','schedules'].map(t=>api('/admin/'+t)));dataSets={categories,products,schedules};Object.keys(configs).forEach(renderAdmin);customers=await api('/admin/customers');renderCustomers();await loadOrders();}
    window.addEventListener('hashchange',page);
    if(admin){
        $('admin-toggle').onclick=()=>{const open=$('admin-nav').classList.toggle('is-open');$('admin-toggle').setAttribute('aria-expanded',String(open));};
        $('order-search').oninput=renderOrders;$('order-date').onchange=renderOrders;$('customer-search').oninput=renderCustomers;$('report-apply').onclick=()=>run(filteredReport);
        $('refresh-admin').onclick=()=>run(refreshAdmin);
        run(refreshAdmin);
    }
    else{
        $('search').oninput=renderCatalog;$('fulfillment').onchange=renderTotals;$('detail-close').onclick=()=>$('food-detail').close();
        $('schedule').onchange=()=>{try{sessionStorage.setItem('meal-schedule',$('schedule').value);}catch{}updateSchedule();};
        if($('refresh-orders'))$('refresh-orders').onclick=()=>run(loadOrders);
        const items=()=>chosen().map(p=>({product_id:p.id,quantity:qty(p)}));
        $('quote').onclick=()=>run(async()=>{if(!items().length)throw Error('Keranjang masih kosong.');const q=await api('/quote','POST',{items:items()});msg('Estimasi subtotal: '+money(q.estimate)+' · ongkir '+money(fee())+' · harga akhir diverifikasi saat checkout.');});
        $('checkout').onclick=async()=>{const b=$('checkout');if(b.disabled)return;b.disabled=true;try{await run(async()=>{
            if(!items().length||!$('schedule').value)throw Error('Pilih menu dan jadwal PO terlebih dahulu.');
            if(role==='guest'){try{sessionStorage.setItem('meal-resume-checkout','1');sessionStorage.setItem('meal-schedule',$('schedule').value);sessionStorage.setItem('meal-notes',$('notes').value);sessionStorage.setItem('meal-delivery',JSON.stringify([$('fulfillment').value,$('delivery-address').value,$('contact-phone').value,$('payment-method').value]));}catch{}location.href='/login';return;}
            if(!confirm('Buat pesanan dengan total estimasi '+money(totalCart()+fee())+'?'))return;
            const o=await api('/orders','POST',{schedule_id:Number($('schedule').value),items:items(),notes:$('notes').value,fulfillment:$('fulfillment').value,delivery_address:$('delivery-address').value,contact_phone:$('contact-phone').value,payment_method:$('payment-method').value});
            cart={};persist();$('notes').value='';$('delivery-address').value='';$('contact-phone').value='';location.hash='pesanan';msg('Pesanan berhasil! '+o.order_code);await catalog();await loadOrders();
        });}finally{updateSchedule();}};
        run(async()=>{await catalog();await loadOrders();});
    }
    if(role==='customer'){try{if(sessionStorage.getItem('meal-resume-checkout')){sessionStorage.removeItem('meal-resume-checkout');$('notes').value=sessionStorage.getItem('meal-notes')||'';sessionStorage.removeItem('meal-notes');const saved=JSON.parse(sessionStorage.getItem('meal-delivery')||'null');if(saved){['fulfillment','delivery-address','contact-phone','payment-method'].forEach((id,i)=>$(id).value=saved[i]);sessionStorage.removeItem('meal-delivery');renderTotals();}location.hash='keranjang';}}catch{}}
    page();
    initialized=true;
}
