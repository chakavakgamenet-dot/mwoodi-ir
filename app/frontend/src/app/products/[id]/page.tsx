'use client';
import {useParams,useRouter} from 'next/navigation';
import {useEffect,useState} from 'react';
import Link from 'next/link';
import {api} from '../../../lib/api';
export default function Product(){
 const {id}=useParams();const [p,setP]=useState<any>();const [msg,setMsg]=useState('');const router=useRouter();
 useEffect(()=>{api<any>('/products/'+id).then(setP).catch(()=>setMsg('محصول پیدا نشد.'))},[id]);
 if(!p) return <section className="page section"><div className="empty">{msg||'در حال بارگذاری…'}</div></section>;
 async function add(){
   try{
    const token=typeof window!=='undefined'?localStorage.getItem('mwoodi_token'):null;
    if(token) await api('/cart/items',{method:'POST',body:JSON.stringify({product_id:p.id,quantity:1})});
    else {const cart=JSON.parse(localStorage.getItem('mwoodi_guest_cart')||'[]');const x=cart.find((x:any)=>x.product_id===p.id);if(x)x.quantity++;else cart.push({product_id:p.id,name:p.name,price:p.price,quantity:1});localStorage.setItem('mwoodi_guest_cart',JSON.stringify(cart));}
    setMsg('✓ محصول به سبد اضافه شد');
   }catch(e){setMsg('برای افزودن به سبد وارد حساب شوید یا خرید میهمان را از سبد ادامه دهید.')}
 }
 return <section className="detail"><div className="detailImage">🪵</div><div><span className="eyebrow dark">{p.category?.name||'MWOODI'}</span><h1>{p.name}</h1><div className="bigPrice">{Number(p.price).toLocaleString('fa-IR')} تومان</div><p>{p.description||'محصولی دست‌ساز با طراحی ساده و گرم، مناسب استفاده روزمره و هدیه.'}</p><div className="detailActions"><button className="btn primary" onClick={add}>افزودن به سبد</button><Link href="/cart" className="btn">رفتن به سبد</Link></div>{msg&&<div className="notice">{msg}</div>}<div className="infoRows"><span>✓ ساخته‌شده با دقت</span><span>✓ بسته‌بندی مناسب</span><span>✓ پشتیبانی قبل و بعد از خرید</span></div></div></section>
}
