'use client';
import {useEffect,useState} from 'react';
import Link from 'next/link';
import {api} from '../../lib/api';

export default function Cart(){
 const [items,setItems]=useState<any[]>([]),[auth,setAuth]=useState(false),[loading,setLoading]=useState(true),[checkout,setCheckout]=useState(false),[busy,setBusy]=useState(false),[notice,setNotice]=useState('');
 const [address,setAddress]=useState({recipient_name:'',recipient_phone:'',province:'',city:'',address:'',postal_code:''});
 useEffect(()=>{
  const t=localStorage.getItem('mwoodi_token'); setAuth(!!t);
  if(t) api<any>('/cart').then(c=>setItems(c.items||[])).catch(e=>setNotice(e.message||'دریافت سبد خرید انجام نشد.')).finally(()=>setLoading(false));
  else {try{setItems(JSON.parse(localStorage.getItem('mwoodi_guest_cart')||'[]'))}catch{setItems([])}setLoading(false)}
 },[]);
 const total=items.reduce((s,x)=>s+Number(x.product?.price??x.price)*x.quantity,0);
 async function placeOrder(){
  if(!address.recipient_name||!address.recipient_phone||!address.province||!address.city||!address.address){setNotice('لطفاً اطلاعات ارسال را کامل کنید.');return}
  setBusy(true);setNotice('');
  try{
   const payload:any={shipping_address:address,shipping_method:'ارسال عادی'};
   if(!auth){
    payload.guest_name=address.recipient_name;
    payload.guest_phone=address.recipient_phone;
    payload.items=items.map(x=>({product_id:x.product_id||x.product?.id,quantity:Number(x.quantity)}));
   }
   const order:any=await api('/orders',{method:'POST',body:JSON.stringify(payload)});
   setItems([]);setCheckout(false);
   if(!auth) localStorage.removeItem('mwoodi_guest_cart');
   setNotice(`سفارش ${order.order_number} با موفقیت ثبت شد.`);
  }catch(e:any){setNotice(e.message||'ثبت سفارش انجام نشد.')}finally{setBusy(false)}
 }
 return <section className="section page"><span className="eyebrow dark">CART</span><div className="sectionTitle"><h1>سبد خرید</h1><span>{auth?'مشتری واردشده':'خرید به‌عنوان میهمان'}</span></div>
 {notice&&<div className="notice">{notice}</div>}
 {loading?<div className="empty">در حال بارگذاری…</div>:!items.length?<div className="empty"><div>🛒</div><h2>سبد خرید شما خالی است</h2><Link href="/products" className="btn primary">مشاهده محصولات</Link></div>:
 <><div className="cartList">{items.map(x=><div className="dashboard" key={x.id||x.product_id}><div><b>{x.product?.name||x.name}</b><span>تعداد: {x.quantity}</span></div><strong>{(Number(x.product?.price??x.price)*x.quantity).toLocaleString('fa-IR')} تومان</strong></div>)}</div>
 <div className="sectionTitle"><strong>جمع: {total.toLocaleString('fa-IR')} تومان</strong><button className="btn primary" onClick={()=>setCheckout(!checkout)}>{checkout?'بستن فرم':'ادامه و ثبت سفارش'}</button></div>
 {checkout&&<div className="authBox" style={{marginTop:20,maxWidth:'none'}}><h2>اطلاعات ارسال</h2><div className="filters"><input placeholder="نام گیرنده" value={address.recipient_name} onChange={e=>setAddress({...address,recipient_name:e.target.value})}/><input placeholder="شماره تماس" dir="ltr" value={address.recipient_phone} onChange={e=>setAddress({...address,recipient_phone:e.target.value})}/></div><div className="filters"><input placeholder="استان" value={address.province} onChange={e=>setAddress({...address,province:e.target.value})}/><input placeholder="شهر" value={address.city} onChange={e=>setAddress({...address,city:e.target.value})}/><input placeholder="کد پستی" dir="ltr" value={address.postal_code} onChange={e=>setAddress({...address,postal_code:e.target.value})}/></div><textarea placeholder="آدرس کامل" value={address.address} onChange={e=>setAddress({...address,address:e.target.value})} style={{minHeight:100,border:'1px solid #ddcfbf',borderRadius:10,padding:12}}/><button className="btn primary" disabled={busy} onClick={placeOrder}>{busy?'در حال ثبت…':'ثبت سفارش'}</button><small>برای تست، ثبت سفارش بدون اتصال درگاه انجام می‌شود و در پنل مدیر قابل مشاهده است.</small></div>}
 </>}
 </section>
}
