'use client';

import Link from 'next/link';
import {useEffect,useMemo,useState} from 'react';
import {api} from '../lib/api';

const fallback = [
 {id:'p1',name:'سینی چوبی دست‌ساز',price:890000,cat:'آشپزخانه',slug:'wood-tray'},
 {id:'p2',name:'استند چوبی مینیمال',price:690000,cat:'دکوراسیون',slug:'minimal-stand'},
 {id:'p3',name:'جعبه پذیرایی چوبی',price:1250000,cat:'پذیرایی',slug:'serving-box'},
 {id:'p4',name:'تخته سرو طبیعی',price:980000,cat:'آشپزخانه',slug:'serving-board'},
 {id:'p5',name:'جا شمعی چوبی',price:490000,cat:'دکوراسیون',slug:'candle-holder'},
 {id:'p6',name:'باکس چوبی رومیزی',price:760000,cat:'دکوراسیون',slug:'desk-box'}
];
const photos=[
 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1200&q=85',
 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&w=900&q=85',
 'https://images.unsplash.com/photo-1600566753051-f0b89df2dd90?auto=format&fit=crop&w=900&q=85',
 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=900&q=85'
];
const money=(n:number)=>Number(n).toLocaleString('fa-IR')+' تومان';

function AuthBadge(){
 const [auth,setAuth]=useState<any>(null),[guest,setGuest]=useState(false);
 useEffect(()=>{const read=()=>{try{setAuth(JSON.parse(localStorage.getItem('mwoodi_auth')||'null'))}catch{setAuth(null)};setGuest(localStorage.getItem('mwoodi_guest')==='1')};read();window.addEventListener('mwoodi-auth-changed',read);return()=>window.removeEventListener('mwoodi-auth-changed',read)},[]);
 if(auth)return <><Link href={['seller','manager','super_admin'].includes(auth.role)?'/admin':'/account'}>{auth.role==='customer'?'👤 '+(auth.name||'حساب من'):'⚙️ مدیریت'}</Link><button className="headerLogout" onClick={async()=>{try{await api('/auth/logout',{method:'POST'})}catch{}localStorage.removeItem('mwoodi_token');localStorage.removeItem('mwoodi_auth');localStorage.setItem('mwoodi_guest','1');window.dispatchEvent(new Event('mwoodi-auth-changed'));location.href='/'}}>خروج</button></>;
 return <>{guest&&<span className="guestBadge">مهمان</span>}<Link href="/login">ورود</Link><Link href="/login?register=1">ثبت‌نام</Link></>;
}


export default function Home(){
 const [products,setProducts]=useState<any[]>(fallback),[loading,setLoading]=useState(true),[q,setQ]=useState('');
 useEffect(()=>{
   api<any[]>('/products?all=1').then(d=>{
     const live=d.map((p:any)=>({...p,cat:p.category?.name||'محصول چوبی'}));
     if(live.length)setProducts(live);
   }).finally(()=>setLoading(false));
 },[]);
 const shown=useMemo(()=>products.filter(p=>!q||String(p.name).includes(q)||String(p.category?.name||p.cat).includes(q)),[products,q]);
 return <main className="mw-home">
   <header className="storeHeader">
    <Link href="/" className="logo"><span className="mark">♨</span><span><b>Mwoodi</b><small>WOODEN ART FOR YOUR HOME</small></span></Link>
    <nav><Link href="/">خانه</Link><Link href="/products">فروشگاه</Link><a href="#cats">دسته‌بندی‌ها</a><a href="#about">درباره ما</a></nav>
    <div className="headActions"><AuthBadge/><input value={q} onChange={e=>setQ(e.target.value)} placeholder="جستجوی محصول..." /><Link href="/cart">🛒 سبد</Link><Link href="/login">ورود</Link><Link href="/login?register=1">ثبت‌نام</Link></div>
   </header>

   <section className="hero">
    <div className="heroCopy"><span className="eyebrow">MWOODI • NATURAL WOOD LIVING</span>
     <h1>چوب را فقط نمی‌فروشیم؛ بخشی از حس خانه می‌کنیم.</h1>
     <p>محصولات چوبی با بافت طبیعی و طراحی کاربردی؛ برای آشپزخانه، پذیرایی و گوشه‌های خاص خانه.</p>
     <div className="actions"><Link href="/products" className="primary">دیدن مجموعه ←</Link><Link href="/login?guest=1" className="secondary">ادامه به‌عنوان میهمان</Link><a href="#cats" className="secondary">انتخاب دسته</a></div>
     <div className="stats"><span>🪵 چوب طبیعی</span><span>✋ پرداخت دست‌ساز</span><span>🏠 برای زندگی روزمره</span></div>
    </div>
   </section>

   <section className="visualBand">{photos.slice(0,3).map((src,i)=><div className="visual" key={src}><img src={src}/><b>{['بافت طبیعی چوب','جزئیات دست‌ساز','گرمای چوب در خانه'][i]}</b></div>)}</section>

   <section id="cats" className="cats">
    {[['آشپزخانه','kitchen'],['پذیرایی','reception'],['اکسسوری','accessories']].map(([c,slug],i)=><Link key={c} href={'/products?category='+slug} className="cat"><img src={photos[i]}/><span><b>{c}</b><small>{i===0?'کاربردی، گرم و ماندگار':i===1?'برای میزهای خاص و مهمانی‌ها':'جزئیات کوچک با شخصیت بزرگ'}</small></span></Link>)}
   </section>

   <section className="collection">
    <div className="sectionHead"><div><span className="eyebrow dark">SELECTED COLLECTION</span><h2>انتخاب‌های Mwoodi</h2><p>محصولات واقعی فروشگاه از دیتابیس</p></div><Link href="/products">مشاهده همه ←</Link></div>
    {loading?<div className="loading">در حال همگام‌سازی با فروشگاه…</div>:<div className="products">{shown.slice(0,8).map((p:any,i:number)=><article className="product" key={p.id}><div className="productImage"><img src={p.images?.[0]?.url||photos[i%photos.length]}/></div><span>{p.category?.name||p.cat}</span><h3>{p.name}</h3><strong>{money(p.price)}</strong><Link href={'/products/'+p.slug} className="productBtn">مشاهده محصول</Link></article>)}</div>}
   </section>

   <section id="about" className="about"><div><span className="eyebrow">ABOUT MWOODI</span><h2>یک فروشگاه نیست؛ یک کارگاه است.</h2><p>سادگی، بافت طبیعی و طراحی کاربردی؛ محصولاتی برای خانه‌های گرم و امروزی.</p></div><div className="trust"><div>🪵<b>کیفیت چوب</b><small>انتخاب‌شده با وسواس</small></div><div>💬<b>پشتیبانی</b><small>قبل و بعد از خرید</small></div><div>📦<b>ارسال مطمئن</b><small>بسته‌بندی مناسب</small></div></div></section>
   <footer>Mwoodi · Wooden Art for Your Home · © 2026</footer>
 </main>;
}
