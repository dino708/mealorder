
from playwright.sync_api import sync_playwright
from pathlib import Path
import re,json,sys
src=Path(sys.argv[1]); out=Path(sys.argv[2]); out.mkdir(exist_ok=True)
css=(src/'resources/css/meal-order.css').read_text()
js=(src/'resources/js/meal.js').read_text().replace("import '../css/meal-order.css';","")
card=(src/'resources/js/components/meal-card.js').read_text()
def html(role):
    t=(src/f'resources/views/meal/{"admin" if role=="admin" else "customer"}.blade.php').read_text()
    if role!='admin':
        t=re.sub(r'@auth(.*?)@else.*?@endauth',r'\1',t,flags=re.S)
        t=t.replace('@auth','').replace('@endauth','')
    t=t.replace("{{ auth()->user()->email }}","customer@example.test").replace("{{ auth()->user()?->phone }}","08123456789")
    t=t.replace('@csrf','').replace("{{ auth()->user()->name }}","Pengelola MealOrder" if role=='admin' else 'Pelanggan MealOrder').replace("{{ auth()->check() ? 'Buat pesanan' : 'Masuk untuk checkout' }}","Buat pesanan")
    t=re.sub(r"{{ asset\('([^']+)'\) }}",r'/\1',t)
    t=re.sub(r"{{ route\('([^']+)'\) }}",r'/\1',t)
    return f'<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="mock"><style>{css}</style></head><body class="mo"><main id="meal-app" data-role="{role}" class="mo-{"admin" if role=="admin" else "shop"}">{t}</main><script>{card}</script><script>{js}</script></body></html>'
cats=[{'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, {'id': 2, 'name': 'Makanan Ringan', 'description': 'Makanan Ringan'}, {'id': 3, 'name': 'Minuman', 'description': 'Minuman'}, {'id': 4, 'name': 'Paket Hemat', 'description': 'Paket Hemat'}, {'id': 5, 'name': 'Dessert', 'description': 'Dessert'}]
products=[{'id': 1, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Nasi Ayam', 'description': 'Nasi putih, ayam bumbu kecap, lalapan dan sambal.', 'price': 15000, 'stock': 30, 'is_available': True, 'image': 'images/menu/nasi-ayam.webp'}, {'id': 2, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Nasi Telur', 'description': 'Nasi hangat, telur mata sapi dan sambal.', 'price': 10000, 'stock': 30, 'is_available': True, 'image': 'images/menu/nasi-telur.webp'}, {'id': 3, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Nasi Goreng', 'description': 'Nasi goreng kecap, sayuran dan telur mata sapi.', 'price': 18000, 'stock': 25, 'is_available': True, 'image': 'images/menu/nasi-goreng.webp'}, {'id': 4, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Ayam Geprek', 'description': 'Ayam renyah dengan sambal cabai dan nasi.', 'price': 17000, 'stock': 25, 'is_available': True, 'image': 'images/menu/ayam-geprek.webp'}, {'id': 5, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Mie Goreng', 'description': 'Mie goreng dengan sawi, wortel dan daun bawang.', 'price': 14000, 'stock': 25, 'is_available': True, 'image': 'images/menu/mie-goreng.webp'}, {'id': 6, 'category_id': 1, 'category': {'id': 1, 'name': 'Makanan Utama', 'description': 'Makanan Utama'}, 'name': 'Sate Ayam', 'description': 'Sate ayam bumbu kacang dan irisan mentimun.', 'price': 20000, 'stock': 20, 'is_available': True, 'image': 'images/menu/sate-ayam.webp'}, {'id': 7, 'category_id': 2, 'category': {'id': 2, 'name': 'Makanan Ringan', 'description': 'Makanan Ringan'}, 'name': 'Risol Mayo', 'description': 'Tiga risol renyah dengan isian telur dan mayones.', 'price': 12000, 'stock': 24, 'is_available': True, 'image': 'images/menu/risol-mayo.webp'}, {'id': 8, 'category_id': 2, 'category': {'id': 2, 'name': 'Makanan Ringan', 'description': 'Makanan Ringan'}, 'name': 'Tahu Isi', 'description': 'Tiga tahu goreng dengan isian sayur.', 'price': 9000, 'stock': 24, 'is_available': True, 'image': 'images/menu/tahu-isi.webp'}, {'id': 9, 'category_id': 3, 'category': {'id': 3, 'name': 'Minuman', 'description': 'Minuman'}, 'name': 'Es Teh Manis', 'description': 'Teh manis dingin dengan es batu.', 'price': 5000, 'stock': 50, 'is_available': True, 'image': 'images/menu/es-teh.webp'}, {'id': 10, 'category_id': 3, 'category': {'id': 3, 'name': 'Minuman', 'description': 'Minuman'}, 'name': 'Es Jeruk', 'description': 'Minuman jeruk segar dengan es batu.', 'price': 8000, 'stock': 40, 'is_available': True, 'image': 'images/menu/es-jeruk.webp'}, {'id': 11, 'category_id': 4, 'category': {'id': 4, 'name': 'Paket Hemat', 'description': 'Paket Hemat'}, 'name': 'Paket Nasi Ayam', 'description': 'Nasi ayam, lalapan dan es teh manis.', 'price': 19000, 'stock': 20, 'is_available': True, 'image': 'images/menu/paket-ayam.webp'}, {'id': 12, 'category_id': 5, 'category': {'id': 5, 'name': 'Dessert', 'description': 'Dessert'}, 'name': 'Puding Cokelat', 'description': 'Puding cokelat lembut dengan saus susu.', 'price': 10000, 'stock': 20, 'is_available': True, 'image': 'images/menu/puding-cokelat.webp'}]
sched=[{'id':1,'title':'PO Makan Siang','start_at':'2026-11-27T00:00:00Z','end_at':'2026-11-28T07:00:00Z','quota':40,'status':'open'}]
orders=[{'id':1,'order_code':'MO-001','status':'pending','user':{'name':'Pelanggan MealOrder'},'user_id':2,'total_amount':'15000.00','items':[{'product_name':'Nasi Ayam','product_image':'images/menu/nasi-ayam.webp','quantity':1,'subtotal':'15000.00'}],'payment':{'status':'unpaid'},'notes':'Sambal dipisah','created_at':'2026-11-27T01:00:00Z'}]
with sync_playwright() as pw:
    browser=pw.chromium.launch(executable_path='/usr/bin/chromium',args=['--no-sandbox'])
    errors=[]; tests=[]
    def page_for(role,viewport):
        page=browser.new_page(viewport=viewport)
        page.on('pageerror',lambda e:errors.append(str(e)))
        page.on('dialog',lambda d:d.accept())
        from base64 import b64encode
        def data_image(name):
            return 'data:image/svg+xml;base64,'+b64encode((src/'public/images'/name).read_bytes()).decode()
        mock="""<script>const mapping=MOCK_DATA;window.fetch=async(path,options={})=>{
          const order=mapping['/api/orders'][0];mapping['/api/admin/orders']=mapping['/api/orders'];
          const method=options.method||'GET';
          let value=mapping[path.split('?')[0]]||{};
          if(method==='PATCH'){
            const payload=JSON.parse(options.body);
            if(path.endsWith('/status'))order.status=payload.status;else order.payment.status=payload.status;
            value=order;
          }
          if(method==='POST'&&path==='/api/orders')value=order;
          return new Response(JSON.stringify(value),{status:method==='POST'&&path==='/api/orders'?201:200,headers:{'Content-Type':'application/json'}});
        };</script>""".replace('MOCK_DATA',json.dumps({'/api/catalog':products,'/api/schedules':sched,'/api/orders':orders,'/api/admin/categories':cats,'/api/admin/products':products,'/api/admin/schedules':sched,'/api/admin/orders':orders,'/api/admin/report':{'sales':0,'completed_orders':0,'customers':1,'daily':{},'transactions':[]},'/api/settings':{'delivery_fee':5000,'delivery_enabled':True},'/api/admin/customers':[{'name':'Pelanggan MealOrder','email':'customer@example.test','orders_count':1}],'/api/quote':{'estimate':15000}})).replace("const order=mapping['/api/orders'][0];mapping['/api/admin/orders']=mapping['/api/orders'];","const order=mapping['/api/orders'][0];mapping['/api/admin/orders']=mapping['/api/orders'];")
        # Persist mock state across fetch calls in the same document.
        mock=mock.replace('window.fetch=async(path,options={})=>{\\n          const mapping=', 'const mapping=')
        mock=mock.replace(";\\n          const order=", ";window.fetch=async(path,options={})=>{\\n          const order=",1)
        import mimetypes
        mapping={}
        for f in (src/'public/images').rglob('*'):
            if f.suffix in ('.webp','.svg','.jpg','.png'):
                mapping['/'+str(f.relative_to(src/'public'))]='data:'+mimetypes.guess_type(f)[0]+';base64,'+b64encode(f.read_bytes()).decode()
        # The sandbox blocks browser HTTP navigation. Resolve local asset bytes in the mock only.
        document=html(role).replace('<script>',mock+'<script>const assets='+json.dumps(mapping)+';const assetSrc=p=>assets[p]||p;</script><script>',1)
        document=document.replace("img.src=path.startsWith('meal-images/')?'/storage/'+path:'/'+path;", "img.src=assetSrc(path.startsWith('meal-images/')?'/storage/'+path:'/'+path);")
        document=document.replace("e.src=localPhoto(p.image);","e.src=assetSrc(localPhoto(p.image));").replace("photoPreview.src=localPhoto(oldPhoto);","photoPreview.src=assetSrc(localPhoto(oldPhoto));")
        document=document.replace('/images/meal-hero.svg',mapping['/images/meal-hero.svg'])
        # Keep placeholder paths within JS untouched except for assignment and markup.
        document=document.replace('src="/images/meal-placeholder.svg"', 'src="'+mapping['/images/meal-placeholder.svg']+'"').replace("e.src='/images/meal-placeholder.svg'","e.src=assetSrc('/images/meal-placeholder.svg')")
        page.set_default_timeout(6000)
        page.set_content(document)
        return page
    p=page_for('customer',{'width':1440,'height':1050})
    p.locator('meal-card').first.wait_for()
    assert p.locator('meal-card').count()==12
    for c in p.locator('meal-card').all():
        c.scroll_into_view_if_needed()
        c.locator('.photo img:not(.placeholder)').evaluate("(img) => img.decode()")
    assert p.locator('meal-card').count()==12
    p.evaluate('window.scrollTo(0,0)')
    p.screenshot(path=str(out/'customer-desktop.png'),full_page=True)
    p.locator('meal-card').first.get_by_role('button',name='Lihat detail').click()
    p.locator('#detail-content .mo-thumb').evaluate("(img)=>img.decode()")
    p.locator('#detail-close').click()
    p.locator('#search').fill('Nasi Telur');assert p.locator('meal-card').count()==1
    p.locator('#search').fill('')
    p.locator('meal-card').first.get_by_role('button',name='+ Tambah',exact=True).click()
    assert '15.000' in p.locator('#basket-preview-total').inner_text()
    p.locator('nav a[data-nav="keranjang"]').click()
    p.locator('#checkout').wait_for()
    assert p.locator('#checkout').is_enabled()
    p.locator('#cart .mo-thumb').first.evaluate('(img)=>img.decode()')
    p.locator('#fulfillment').select_option('delivery');p.locator('#delivery-address').fill('Jalan Melati');p.locator('#contact-phone').fill('08123456789')
    assert '20.000' in p.locator('#checkout-totals').inner_text()
    p.locator('#quote').click();p.get_by_text('Estimasi subtotal:',exact=False).wait_for()
    p.locator('#checkout').click();p.locator('[data-page=pesanan]').wait_for()
    p.get_by_text('MO-001',exact=True).wait_for()
    p.locator('#orders .mo-thumb').first.evaluate('(img)=>img.decode()')
    tests.append('customer: katalog, pencarian, tambah keranjang, estimasi dan checkout API mock')
    p.close()
    p=page_for('customer',{'width':390,'height':844})
    p.locator('meal-card').first.wait_for()
    assert p.evaluate('document.documentElement.scrollWidth <= innerWidth')
    for c in p.locator('meal-card').all():
        c.scroll_into_view_if_needed()
        c.locator('.photo img:not(.placeholder)').evaluate("(img) => img.decode()")
    p.evaluate('window.scrollTo(0,0)')
    p.screenshot(path=str(out/'customer-mobile.png'),full_page=True)
    tests.append('customer mobile 390px: tidak overflow horizontal')
    p.close()
    p=page_for('admin',{'width':1440,'height':1000})
    p.locator('#metrics .mo-metric').first.wait_for()
    p.screenshot(path=str(out/'admin-desktop.png'),full_page=True)
    p.locator('a[data-nav=pelanggan]').click();p.get_by_text('customer@example.test',exact=False).wait_for();p.locator('a[data-nav=laporan]').click();p.locator('#report-apply').click();p.locator('a[data-nav=menu]').click()
    p.get_by_role('button',name='+ Tambah Menu').click()
    p.locator('input[type=file]').wait_for()
    assert p.locator('#admin-products .mo-admin-list').is_hidden()
    p.locator('input[type=file]').set_input_files(str(src/'public/images/menu/nasi-ayam.webp'))
    assert p.locator('.mo-upload-preview').is_visible()
    assert 'Foto baru' in p.locator('.mo-upload-copy').inner_text()
    p.locator('.mo-upload-preview').evaluate("(img)=>img.decode()")
    p.screenshot(path=str(out/'admin-form.png'),full_page=True)
    p.locator('input[type=file]').set_input_files({'name':'script.php','mimeType':'text/plain','buffer':b'<?php echo 1; ?>'})
    assert 'File tidak valid' in p.locator('.mo-upload-copy').inner_text()
    p.get_by_role('button',name='Batalkan pilihan foto baru').click()
    tests.append('admin: preview WebP valid, pilihan file invalid ditolak UI, batal foto')

    p.get_by_role('button',name='Batal',exact=True).click()
    assert p.locator('#admin-products .mo-admin-list').is_visible()
    p.locator('a[data-nav=pesanan]').click()
    p.get_by_role('button',name='Terima pesanan').click()
    p.get_by_role('button',name='Mulai memasak').wait_for()
    p.get_by_role('button',name='Mulai memasak').click()
    p.get_by_role('button',name='Tandai siap diambil').click()
    p.get_by_role('button',name='Selesaikan pesanan').wait_for()
    assert p.get_by_role('button',name='Selesaikan pesanan').is_disabled()
    p.get_by_role('button',name='Konfirmasi pembayaran').click()
    p.get_by_role('button',name='Selesaikan pesanan').click()
    p.get_by_text('Selesai',exact=True).first.wait_for()
    tests.append('admin: navigasi, buka/batal form menu, perubahan status, konfirmasi pembayaran mock')
    p.close()
    p=page_for('admin',{'width':390,'height':844})
    p.locator('#metrics .mo-metric').first.wait_for()
    p.locator('#admin-toggle').click()
    p.locator('a[data-nav=menu]').click()
    assert p.evaluate('document.documentElement.scrollWidth <= innerWidth')
    tests.append('admin mobile: navigasi dan tidak overflow horizontal')
    p.close()
    p=page_for('customer',{'width':768,'height':1024})
    p.locator('meal-card').first.wait_for()
    assert p.evaluate('document.documentElement.scrollWidth <= innerWidth')
    tests.append('customer tablet 768px: tidak overflow horizontal')
    p.close();browser.close()
    assert not errors,errors
    print('\n'.join(tests))
