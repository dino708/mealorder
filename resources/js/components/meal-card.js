const rupiah = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0,maximumFractionDigits:2}).format(n);
class MealCard extends HTMLElement {
    constructor(){super();this.attachShadow({mode:'open'});}
    get productId(){return this._product?.id;}
    set quantity(n){this._quantity=n;this.updateButton();}
    updateButton(){const b=this.shadowRoot.querySelector('.foot button');if(!b||!this._product)return;const stock=Math.min(100,Number(this._product.stock)||0);b.disabled=stock<=0||(this._quantity||0)>=stock;b.textContent=stock<=0?'Habis':b.disabled?'Maksimal':(this._quantity||0)>0?`+ Tambah (${this._quantity})`:'+ Tambah';}
    set product(p){
        this._product=p;this._quantity=0;
        this.shadowRoot.innerHTML=`<style>
        :host{display:block;min-width:0}article{height:100%;transition:box-shadow .18s,border-color .18s;box-sizing:border-box;background:white;border:1px solid #e9dfd5;border-radius:20px;overflow:hidden;display:flex;flex-direction:column;font:inherit}
        article:hover{border-color:#f0b588;box-shadow:0 8px 24px #103b3110}.photo{aspect-ratio:4/3;background:#fff0df;display:grid;place-items:center;position:relative;overflow:hidden}.photo span{font-size:48px}.photo small{position:absolute;bottom:8px;left:8px;color:#71543d;font-size:10px;font-weight:600;background:#fffdf4;padding:3px 7px;border-radius:6px;z-index:1}.photo:has(img:not(.placeholder)) small{display:none}.photo .placeholder{object-fit:contain;padding:14px}.photo img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
        .body{padding:16px;flex:1;display:flex;flex-direction:column}.category{font-size:11px;color:#b9430b;font-weight:700;text-transform:uppercase;letter-spacing:.8px}
        h3{font-size:18px;line-height:1.25;margin:6px 0;color:#31251e;overflow-wrap:anywhere}.desc{font-size:13px;color:#70675f;margin:3px 0 10px;overflow-wrap:anywhere}.stock{font-size:12px;color:#70675f;margin:auto 0 10px}.foot{display:flex;align-items:center;justify-content:space-between;gap:8px}.price{font-size:17px;color:#b9430b;font-weight:800;margin:0}
        button{background:#b9430b;color:white;border:0;border-radius:10px;padding:10px 13px;font:inherit;font-weight:700;cursor:pointer;white-space:nowrap}button:disabled{opacity:.45;cursor:default}button:focus-visible{outline:3px solid #d69b37;outline-offset:3px}
        button.details{background:transparent;color:#b9470b;padding:4px 0;text-align:left;font-size:12px;margin-bottom:8px;white-space:normal}
        @media(max-width:520px){.photo{aspect-ratio:4/3}.photo span{font-size:45px}.body{padding:10px}.foot{flex-wrap:wrap}.price{font-size:14px}h3{font-size:15px}button{font-size:12px;padding:8px 10px}}
        </style><article><div class="photo"><img class="placeholder" src="/images/meal-placeholder.svg" alt=""><small>Foto belum tersedia</small></div><div class="body"><div class="category"></div><h3></h3><p class="desc"></p><p class="stock"></p><button type="button" class="details">Lihat detail</button><div class="foot"><p class="price"></p><button type="button"></button></div></div></article>`;
        const $=s=>this.shadowRoot.querySelector(s);
        const path=String(p.image||'').replace(/^\/?storage\//,'').replace(/^\//,'');
        if(/^(?:meal-images|images)\/[a-zA-Z0-9_./-]+$/.test(path)&&!path.includes('..')){const img=document.createElement('img');img.src=path.startsWith('meal-images/')?'/storage/'+path:'/'+path;img.alt=p.name;img.loading='lazy';img.onerror=()=>img.remove();$('.photo').append(img);}
        $('.category').textContent=p.category?.name||'Menu';$('h3').textContent=p.name;$('.desc').textContent=p.description||'Pesan sesuai jadwal pre-order.';
        $('.price').textContent=rupiah(p.price);$('.stock').textContent=p.stock>0?`Tersedia ${p.stock} porsi`:'Stok habis';
        this.updateButton();
        $('.details').onclick=()=>this.dispatchEvent(new CustomEvent('detail-meal',{detail:p,bubbles:true,composed:true}));
        $('.foot button').onclick=()=>this.dispatchEvent(new CustomEvent('add-meal',{detail:p,bubbles:true,composed:true}));
    }
}
if(!customElements.get('meal-card'))customElements.define('meal-card',MealCard);
