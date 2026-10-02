'use client';
import {useEffect,useState} from 'react';
import Link from 'next/link';
import {api,clearToken} from '../../lib/api';

type Settings={storefront:any;trust:any;payment:any};

export default function Admin(){
 const [stats,setStats]=useState<any>(null),[orders,setOrders]=useState<any[]>([]),[products,setProducts]=useState<any[]>([]);
 const [customers,setCustomers]=useState<any[]>([]),[settings,setSettings]=useState<Settings>({storefront:{},trust:{items:[]},payment:{}});
 const [tab,setTab]=useState<'dashboard'|'customers'|'settings'>('dashboard'),[error,setError]=useState(''),[loading,setLoading]=useState(true),[saving,setSaving]=useState(false),[saved,setSaved]=useState('');

 async function load(){
  setLoading(true);setError('');
  try{
   const [s,o,p,c,st]=await Promise.all([
    api<any>('/admin/dashboard'),api<any>('/admin/orders'),api<any>('/admin/products'),
    api<any>('/admin/customers'),api<any>('/admin/settings')
   ]);
   setStats(s);setOrders(o.data||[]);setProducts(p.data||[]);setCustomers(c.data||[]);
   setSettings({storefront:st.storefront||{},trust:st.trust||{items:[]},payment:st.payment||{}});
  }catch(e:any){
   const message=String(e?.message||'دریافت اطلاعات پنل انجام نشد.');
   if(message.includes('401')||message.includes('403')){clearToken();location.href='/login?admin=1';return;}
   setError(message);
  }finally{setLoading(false)}
 }
 useEffect(()=>{load()},[]);

 function logout(){api('/auth/logout',{method:'POST'}).catch(()=>{}).finally(()=>{clearToken();localStorage.removeItem('mwoodi_auth');location.href='/login?admin=1'})}
 async function toggleProduct(p:any){
  try{await api('/admin/products/'+p.id,{method:'PATCH',body:JSON.stringify({is_active:!p.is_active})});setProducts(xs=>xs.map(x=>x.id===p.id?{...x,is_active:!x.is_active}:x))}
  catch(e:any){setError(e.message)}
 }
 async function saveSettings(){
  setSaving(true);setSaved('');setError('');
  try{const st=await api<any>('/admin/settings',{method:'PUT',body:JSON.stringify(settings)});setSettings({storefront:st.storefront||{},trust:st.trust||{items:[]},payment:st.payment||{}});setSaved('تنظیمات با موفقیت ذخیره شد.')}
  catch(e:any){setError(e.message||'ذخیره تنظیمات انجام نشد.')}finally{setSaving(false)}
 }
 function setStore(key:string,value:string){setSettings(x=>({...x,storefront:{...x.storefront,[key]:value}}))}
 if(loading)return <section className="section page"><div className="empty">در حال بارگذاری کامل پنل مدیریت…</div></section>;

 return <section className="section page">
  <div className="sectionTitle"><div><span className="eyebrow dark">MWOODI ADMIN</span><h1>پنل مدیریت</h1><p>مدیریت فروشگاه، مشتریان و تنظیمات سایت در یک مسیر واحد.</p></div><div className="detailActions"><Link href="/" className="btn">سایت</Link><button onClick={logout} className="btn">خروج</button></div></div>
  <div className="filters">
   <button className={'btn '+(tab==='dashboard'?'primary':'')} onClick={()=>setTab('dashboard')}>داشبورد</button>
   <button className={'btn '+(tab==='customers'?'primary':'')} onClick={()=>setTab('customers')}>مشتریان</button>
   <button className={'btn '+(tab==='settings'?'primary':'')} onClick={()=>setTab('settings')}>⚙️ تنظیمات سایت</button>
  </div>
  {error&&<div className="error">{error}</div>}{saved&&<div className="notice">{saved}</div>}

  {tab==='dashboard'&&<><div className="adminCards">
   <div><span>فروش پرداخت‌شده امروز</span><b>{Number(stats?.sales_today||0).toLocaleString('fa-IR')} تومان</b></div>
   <div><span>سفارش‌ها</span><b>{stats?.orders||0}</b></div><div><span>در انتظار</span><b>{stats?.pending_orders||0}</b></div><div><span>مشتریان</span><b>{stats?.customers||0}</b></div>
  </div><div className="adminGrid"><div><h2>آخرین سفارش‌ها</h2>{orders.length?orders.slice(0,12).map(o=><div className="dashboard" key={o.id}><div><b>{o.order_number}</b><span>{o.customer_name_snapshot} · {o.order_status}</span></div><strong>{Number(o.total).toLocaleString('fa-IR')} تومان</strong></div>):<div className="empty compact">سفارشی ثبت نشده است.</div>}</div>
   <div><h2>محصولات</h2>{products.map(p=><div className="dashboard" key={p.id}><div><b>{p.name}</b><span>{Number(p.price).toLocaleString('fa-IR')} تومان</span></div><button className="btn small" onClick={()=>toggleProduct(p)}>{p.is_active?'فعال':'غیرفعال'}</button></div>)}</div></div></>}

  {tab==='customers'&&<div className="adminGrid"><div style={{gridColumn:'1/-1'}}><h2>مشتریان ثبت‌نام‌شده</h2>{customers.length?customers.map(c=><div className="dashboard" key={c.id}><div><b>{c.name}</b><span>{c.customer_no||'—'} · {c.phone} · کد ملی: {c.national_id||'ثبت نشده'}</span></div><span className={c.is_active?'notice':'error'}>{c.is_active?'فعال':'غیرفعال'}</span></div>):<div className="empty compact">هنوز مشتری ثبت‌نام نکرده است.</div>}</div></div>}

  {tab==='settings'&&<div className="authBox" style={{maxWidth:'none'}}>
   <h2>تنظیمات محتوای سایت</h2>
   <div className="filters">
    <input value={settings.storefront.title||''} onChange={e=>setStore('title',e.target.value)} placeholder="عنوان سایت"/>
    <input value={settings.storefront.hero_title||''} onChange={e=>setStore('hero_title',e.target.value)} placeholder="عنوان اصلی"/>
   </div>
   <textarea value={settings.storefront.hero_text||''} onChange={e=>setStore('hero_text',e.target.value)} placeholder="متن معرفی صفحه اصلی" style={{minHeight:90}}/>
   <div className="filters"><input value={settings.storefront.about_title||''} onChange={e=>setStore('about_title',e.target.value)} placeholder="عنوان درباره ما"/><input value={settings.storefront.contact||''} onChange={e=>setStore('contact',e.target.value)} placeholder="شماره/راه تماس"/></div>
   <textarea value={settings.storefront.about_text||''} onChange={e=>setStore('about_text',e.target.value)} placeholder="متن درباره ما" style={{minHeight:90}}/>
   <div className="filters"><input value={settings.storefront.address||''} onChange={e=>setStore('address',e.target.value)} placeholder="آدرس فروشگاه"/><input value={settings.storefront.shipping||''} onChange={e=>setStore('shipping',e.target.value)} placeholder="روش/توضیح ارسال"/></div>
   <div className="filters"><input value={settings.storefront.instagram||''} onChange={e=>setStore('instagram',e.target.value)} placeholder="لینک Instagram"/><input value={settings.storefront.telegram||''} onChange={e=>setStore('telegram',e.target.value)} placeholder="لینک Telegram"/></div>
   <h3>اعتماد و خدمات</h3><textarea value={(settings.trust.items||[]).join('\n')} onChange={e=>setSettings(x=>({...x,trust:{...x.trust,items:e.target.value.split('\n').map(v=>v.trim()).filter(Boolean)}}))} placeholder="هر مورد در یک خط" style={{minHeight:110}}/>
   <h3>پرداخت</h3><div className="filters"><select value={settings.payment.mode||'manual'} onChange={e=>setSettings(x=>({...x,payment:{...x.payment,mode:e.target.value}}))}><option value="manual">ثبت سفارش / پرداخت دستی</option><option value="gateway">درگاه اینترنتی (پس از اتصال)</option></select><input value={settings.payment.gateway_name||''} onChange={e=>setSettings(x=>({...x,payment:{...x.payment,gateway_name:e.target.value}}))} placeholder="نام درگاه"/></div>
   <div className="filters"><input value={settings.payment.card_holder||''} onChange={e=>setSettings(x=>({...x,payment:{...x.payment,card_holder:e.target.value}}))} placeholder="نام صاحب کارت"/><input value={settings.payment.card_number||''} onChange={e=>setSettings(x=>({...x,payment:{...x.payment,card_number:e.target.value}}))} placeholder="شماره کارت"/></div>
   <button className="btn primary" disabled={saving} onClick={saveSettings}>{saving?'در حال ذخیره…':'ذخیره تنظیمات سایت'}</button>
  </div>}
 </section>;
}
