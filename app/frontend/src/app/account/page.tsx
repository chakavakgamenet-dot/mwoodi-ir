'use client';
import {useEffect,useState} from 'react';
import {useRouter} from 'next/navigation';
import Link from 'next/link';
import {api,clearToken} from '../../lib/api';

export default function Account(){
 const [me,setMe]=useState<any>(null),[orders,setOrders]=useState<any[]>([]),[loading,setLoading]=useState(true),[editing,setEditing]=useState(false),[busy,setBusy]=useState(false),[notice,setNotice]=useState('');
 const [form,setForm]=useState({name:'',phone:'',national_id:'',password:''});const router=useRouter();
 useEffect(()=>{Promise.all([api<any>('/auth/me'),api<any>('/orders')]).then(([m,o])=>{setMe(m.customer);setForm({name:m.customer?.name||'',phone:m.customer?.phone||'',national_id:m.customer?.national_id||'',password:''});setOrders(o.data||[])}).catch(()=>router.push('/login')).finally(()=>setLoading(false))},[router]);
 if(loading)return <section className="section page"><div className="empty">در حال بارگذاری حساب…</div></section>;
 async function save(){setBusy(true);setNotice('');try{const body:any={name:form.name,phone:form.phone,national_id:form.national_id||null};if(form.password)body.password=form.password;const r=await api<any>('/auth/profile',{method:'PATCH',body:JSON.stringify(body)});setMe(r.customer);setForm(x=>({...x,password:''}));setEditing(false);setNotice('اطلاعات حساب با موفقیت ذخیره شد.')}catch(e:any){setNotice(e.message)}finally{setBusy(false)}}
 function logout(){api('/auth/logout',{method:'POST'}).catch(()=>{}).finally(()=>{clearToken();localStorage.removeItem('mwoodi_auth');localStorage.setItem('mwoodi_guest','1');router.push('/')})}
 return <section className="section page"><span className="eyebrow dark">ACCOUNT</span><div className="sectionTitle"><div><h1>حساب من</h1><p>سلام، {me?.name||'مشتری MWoodi'}</p></div><div className="detailActions"><button className="btn" onClick={()=>setEditing(!editing)}>{editing?'بستن ویرایش':'ویرایش حساب'}</button><button className="btn" onClick={logout}>خروج از حساب</button></div></div>
 {notice&&<div className="notice">{notice}</div>}
 {editing?<div className="authBox" style={{maxWidth:'none'}}><div className="filters"><input value={form.name} onChange={e=>setForm({...form,name:e.target.value})} placeholder="نام و نام خانوادگی"/><input value={form.phone} onChange={e=>setForm({...form,phone:e.target.value})} placeholder="شماره موبایل" dir="ltr"/><input value={form.national_id} onChange={e=>setForm({...form,national_id:e.target.value})} placeholder="کد ملی" dir="ltr"/><input value={form.password} onChange={e=>setForm({...form,password:e.target.value})} placeholder="رمز جدید؛ اختیاری" type="password" dir="ltr"/></div><button className="btn primary" disabled={busy} onClick={save}>{busy?'در حال ذخیره…':'ذخیره تغییرات'}</button></div>:<div className="dashboard"><div><b>{me?.phone}</b><span>شماره مشتری: {me?.customer_no||'—'} · کد ملی: {me?.national_id||'ثبت نشده'}</span></div><Link href="/products" className="btn">ادامه خرید</Link></div>}
 <h2>سفارش‌های من</h2>{orders.length?<div className="adminGrid">{orders.map(o=><div key={o.id}><b>{o.order_number}</b><p>{Number(o.total).toLocaleString('fa-IR')} تومان</p><span>{o.order_status} · {o.payment_status}</span></div>)}</div>:<div className="empty compact">هنوز سفارشی ثبت نشده است.</div>}
 </section>;
}
