'use client';
import {useEffect,useState} from 'react';
import Link from 'next/link';
import {api,clearToken} from '../../lib/api';
export default function Admin(){
 const [stats,setStats]=useState<any>(null),[orders,setOrders]=useState<any[]>([]),[products,setProducts]=useState<any[]>([]),[error,setError]=useState(''),[loading,setLoading]=useState(true);
 useEffect(()=>{
  Promise.allSettled([api<any>('/admin/dashboard'),api<any>('/admin/orders'),api<any>('/admin/products')])
   .then(results=>{
    const failed=results.find(x=>x.status==='rejected') as PromiseRejectedResult|undefined;
    if(failed){
      const message=String(failed.reason?.message||'');
      if(message.includes('401')||message.includes('403')){
        clearToken();
        location.href='/login?admin=1';
        return;
      }
      setError(message||'دریافت اطلاعات پنل انجام نشد.');
    }
    const [s,o,p]=results;
    if(s.status==='fulfilled') setStats(s.value);
    if(o.status==='fulfilled') setOrders(o.value.data||[]);
    if(p.status==='fulfilled') setProducts(p.value.data||[]);
   })
   .finally(()=>setLoading(false));
 },[]);
 function logout(){api('/auth/logout',{method:'POST'}).catch(()=>{}).finally(()=>{clearToken();location.href='/login?admin=1'})}
 async function toggle(p:any){try{await api('/admin/products/'+p.id,{method:'PATCH',body:JSON.stringify({is_active:!p.is_active})});setProducts(xs=>xs.map(x=>x.id===p.id?{...x,is_active:!x.is_active}:x))}catch(e:any){setError(e.message)}}
 if(loading)return <section className="section page"><div className="empty">در حال بارگذاری پنل مدیریت…</div></section>;
 return <section className="section page"><div className="sectionTitle"><div><span className="eyebrow dark">MWOODI ADMIN</span><h1>داشبورد مدیریت</h1></div><div className="detailActions"><Link href="/" className="btn">سایت</Link><button onClick={logout} className="btn">خروج</button></div></div>
 {error?<div className="error">{error}<div><Link href="/login?admin=1" className="btn small">ورود مدیر</Link></div></div>:<><div className="adminCards"><div><span>فروش امروز</span><b>{Number(stats?.sales_today||0).toLocaleString('fa-IR')} تومان</b></div><div><span>سفارش‌ها</span><b>{stats?.orders||0}</b></div><div><span>در انتظار</span><b>{stats?.pending_orders||0}</b></div><div><span>محصولات</span><b>{stats?.products||0}</b></div></div><div className="adminGrid"><div><h2>آخرین سفارش‌ها</h2>{orders.length?orders.slice(0,10).map(o=><div className="dashboard" key={o.id}><div><b>{o.order_number}</b><span>{o.customer_name_snapshot} · {o.order_status}</span></div><strong>{Number(o.total).toLocaleString('fa-IR')} تومان</strong></div>):<div className="empty compact">سفارشی ثبت نشده است.</div>}</div><div><h2>محصولات</h2>{products.map(p=><div className="dashboard" key={p.id}><div><b>{p.name}</b><span>{Number(p.price).toLocaleString('fa-IR')} تومان</span></div><button className="btn small" onClick={()=>toggle(p)}>{p.is_active?'فعال':'غیرفعال'}</button></div>)}</div></div></>}
 </section>
}
