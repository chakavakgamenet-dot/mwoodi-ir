'use client';
import {useEffect,useState} from 'react';
import {useRouter} from 'next/navigation';
import Link from 'next/link';
import {api,clearToken} from '../../lib/api';
export default function Account(){
 const [me,setMe]=useState<any>(null),[orders,setOrders]=useState<any[]>([]),[loading,setLoading]=useState(true);const router=useRouter();
 useEffect(()=>{Promise.all([api<any>('/auth/me'),api<any>('/orders')]).then(([m,o])=>{setMe(m.customer);setOrders(o.data||[])}).catch(()=>router.push('/login')).finally(()=>setLoading(false))},[router]);
 if(loading)return <section className="section page"><div className="empty">در حال بارگذاری حساب…</div></section>;
 function logout(){api('/auth/logout',{method:'POST'}).catch(()=>{}).finally(()=>{clearToken();router.push('/')})}
 return <section className="section page"><span className="eyebrow dark">ACCOUNT</span><div className="sectionTitle"><div><h1>حساب من</h1><p>سلام، {me?.name||'مشتری MWoodi'}</p></div><button className="btn" onClick={logout}>خروج از حساب</button></div>
 <div className="dashboard"><div><b>{me?.phone}</b><span>حساب مشتری فعال</span></div><Link href="/products" className="btn">ادامه خرید</Link></div>
 <h2>سفارش‌های من</h2>{orders.length?<div className="adminGrid">{orders.map(o=><div key={o.id}><b>{o.order_number}</b><p>{Number(o.total).toLocaleString('fa-IR')} تومان</p><span>{o.order_status}</span></div>)}</div>:<div className="empty compact">هنوز سفارشی ثبت نشده است.</div>}
 </section>
}
