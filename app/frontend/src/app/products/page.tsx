'use client';
import Link from 'next/link';
import {useEffect,useState} from 'react';
import {api} from '../../lib/api';
const money=(n:number)=>Number(n).toLocaleString('fa-IR')+' تومان';
const fallback=[
 {id:'21000000-0000-0000-0000-000000000001',name:'سرویس پذیرایی چوبی ۶ نفره',slug:'wood-serving-set-6',price:4800000,category:{name:'پذیرایی'}},
 {id:'21000000-0000-0000-0000-000000000002',name:'سرویس آشپزخانه ۷ تکه',slug:'wood-kitchen-set-7',price:3200000,category:{name:'آشپزخانه'}},
 {id:'21000000-0000-0000-0000-000000000003',name:'تخته سرو گرد',slug:'round-serving-board',price:850000,category:{name:'اکسسوری'}},
 {id:'21000000-0000-0000-0000-000000000004',name:'کاسه چوبی دست‌ساز',slug:'handmade-wood-bowl',price:1250000,category:{name:'اکسسوری'}},
 {id:'21000000-0000-0000-0000-000000000005',name:'ست قاشق و کفگیر',slug:'wood-spoon-spatula-set',price:1450000,category:{name:'آشپزخانه'}},
 {id:'21000000-0000-0000-0000-000000000006',name:'سینی پذیرایی مستطیل',slug:'rectangular-serving-tray',price:2100000,category:{name:'پذیرایی'}}
];
export default function Products(){
 const [items,setItems]=useState<any[]>([]),[q,setQ]=useState(''),[loading,setLoading]=useState(true),[notice,setNotice]=useState('');
 useEffect(()=>{api<any>('/products').then(d=>{const live=d.data||[];setItems(live.length?live:fallback)}).catch(()=>{setItems(fallback);setNotice('نمایش محصولات از فهرست فروشگاه انجام شد؛ برای ثبت سفارش باید ارتباط سرور برقرار باشد.')}).finally(()=>setLoading(false))},[]);
 async function add(p:any){
  try{
   const token=localStorage.getItem('mwoodi_token');
   if(token) await api('/cart/items',{method:'POST',body:JSON.stringify({product_id:p.id,quantity:1})});
   else {const cart=JSON.parse(localStorage.getItem('mwoodi_guest_cart')||'[]');const x=cart.find((x:any)=>x.product_id===p.id);if(x)x.quantity++;else cart.push({product_id:p.id,name:p.name,price:p.price,quantity:1});localStorage.setItem('mwoodi_guest_cart',JSON.stringify(cart));}
   setNotice(`«${p.name}» به سبد خرید اضافه شد.`);
  }catch(e:any){setNotice(e.message||'افزودن به سبد انجام نشد.')}
 }
 const filtered=items.filter(p=>!q||p.name.includes(q));
 return <section className="section page"><div className="sectionTitle"><div><span className="eyebrow dark">SHOP • مهمان هم می‌تواند خرید کند</span><h1>محصولات MWoodi</h1></div><div className="detailActions"><Link href="/login?guest=1" className="btn">ادامه به‌عنوان میهمان</Link><Link href="/cart" className="btn primary">سبد خرید</Link></div></div>
 <div className="filters"><input placeholder="جستجوی محصول..." value={q} onChange={e=>setQ(e.target.value)}/></div>
 {notice&&<div className="notice">{notice}</div>}
 {loading?<div className="empty">در حال بارگذاری محصولات…</div>:!filtered.length?<div className="empty">محصولی برای نمایش وجود ندارد.</div>:<div className="products">{filtered.map(p=><article className="product" key={p.id}><div className="productImage">🪵</div><span className="muted">{p.category?.name||'محصول چوبی'}</span><h3>{p.name}</h3><strong>{money(p.price)}</strong><div className="detailActions"><Link href={'/products/'+p.slug} className="btn small">مشاهده</Link><button className="btn primary small" onClick={()=>add(p)}>افزودن به سبد</button></div></article>)}</div>}
 </section>
}
