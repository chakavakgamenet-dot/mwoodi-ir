'use client';
import Link from 'next/link';
import {useEffect,useState} from 'react';
import {usePathname} from 'next/navigation';
import {api,clearToken} from '../lib/api';

type Auth={role?:string;name?:string;phone?:string};
export default function StoreHeader(){
 const pathname=usePathname();
 const [auth,setAuth]=useState<Auth|null>(null),[guest,setGuest]=useState(false);
 useEffect(()=>{
  const read=()=>{try{setAuth(JSON.parse(localStorage.getItem('mwoodi_auth')||'null'))}catch{setAuth(null)};setGuest(localStorage.getItem('mwoodi_guest')==='1')};
  read(); window.addEventListener('mwoodi-auth-changed',read); window.addEventListener('storage',read); return()=>{window.removeEventListener('mwoodi-auth-changed',read);window.removeEventListener('storage',read)};
 },[]);
 if(pathname==='/') return null;
 async function logout(){try{if(localStorage.getItem('mwoodi_token')) await api('/auth/logout',{method:'POST'})}catch{} finally{clearToken();localStorage.removeItem('mwoodi_auth');localStorage.setItem('mwoodi_guest','1');window.dispatchEvent(new Event('mwoodi-auth-changed'));location.href='/';}}
 const admin=auth&&['seller','manager','super_admin','admin'].includes(auth.role||'');
 return <header className="header">
  <Link href="/" className="logo"><span className="mark">♨</span><span><b>MWoodi</b><small>WOOD · CRAFT · HOME</small></span></Link>
  <nav><Link href="/">خانه</Link><Link href="/products">محصولات</Link><Link href="/#about">درباره ما</Link>{admin&&<Link href="/admin">فروش و مدیریت</Link>}</nav>
  <div className="actions"><Link href="/cart" className="cart">🛒 سبد خرید</Link>{auth?<><Link href={admin?'/admin':'/account'} className="userLink">{admin?'⚙️ مدیر':'👤 '+(auth.name||'حساب من')}</Link><button className="headerLogout" onClick={logout}>خروج</button></>:guest?<><span className="guestBadge">میهمان</span><Link href="/login">ورود مشتری</Link></>:<><Link href="/login">ورود</Link><Link href="/login?register=1">ثبت‌نام</Link></>}</div>
 </header>;
}
