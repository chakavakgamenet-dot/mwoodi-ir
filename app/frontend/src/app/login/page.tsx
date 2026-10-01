'use client';
import {useEffect,useState} from 'react';
import {useRouter} from 'next/navigation';
import Link from 'next/link';
import {api,apiNetworkSafe,saveToken} from '../../lib/api';

async function mergeGuestCart(){
 if(typeof window==='undefined') return;
 try{
  const raw=localStorage.getItem('mwoodi_guest_cart');
  const cart=raw?JSON.parse(raw):[];
  if(!Array.isArray(cart)||!cart.length) return;
  for(const item of cart){
   if(item?.product_id && Number(item.quantity)>0){
    await api('/cart/items',{method:'POST',body:JSON.stringify({product_id:item.product_id,quantity:Number(item.quantity)})});
   }
  }
  localStorage.removeItem('mwoodi_guest_cart');
 }catch{}
}

export default function Login(){
 const [admin,setAdmin]=useState(false);
 const [guest,setGuest]=useState(false);
 useEffect(()=>{const sp=new URLSearchParams(window.location.search);setAdmin(sp.get('admin')==='1');setGuest(sp.get('guest')==='1');setRegister(sp.get('register')==='1')},[]);
 const [register,setRegister]=useState(false),[name,setName]=useState(''),[phone,setPhone]=useState(''),[nationalId,setNationalId]=useState(''),[username,setUsername]=useState('admin'),[password,setPassword]=useState(''),[password2,setPassword2]=useState(''),[error,setError]=useState(''),[busy,setBusy]=useState(false);
 const router=useRouter();
 async function submit(){
  setBusy(true);setError('');
  try{
   if(!admin && register && password!==password2) throw new Error('تکرار رمز عبور با رمز اصلی یکسان نیست.');
   if(!admin && register && password.length<4) throw new Error('رمز عبور باید حداقل ۴ کاراکتر باشد.');
   const data:any=await apiNetworkSafe(admin?'/auth/admin-login':register?'/auth/register':'/auth/login',{method:'POST',body:JSON.stringify(admin?{username:username.trim(),password}:register?{name:name.trim(),phone:phone.trim(),national_id:nationalId.replace(/\D/g,''),password}:{identifier:phone.trim(),password})});
   saveToken(data.token);
   if(!admin) await mergeGuestCart();
   router.push(admin?'/admin':'/account');
  }catch(e:any){setError(e.message||'ورود/ثبت‌نام انجام نشد. اطلاعات را بررسی کنید.');}
  finally{setBusy(false)}
 }
 return <section className="auth"><div className="authBox">
  <span className="eyebrow dark">{admin?'MWOODI ADMIN':'MWOODI ACCOUNT'}</span><h1>{admin?'ورود مدیر':register?'ساخت حساب':'ورود به حساب'}</h1>
  {admin?<input value={username} onChange={e=>setUsername(e.target.value)} placeholder="نام کاربری یا ایمیل" dir="ltr"/>:register&&<><input value={name} onChange={e=>setName(e.target.value)} placeholder="نام و نام خانوادگی"/><input value={nationalId} onChange={e=>setNationalId(e.target.value)} placeholder="کد ملی (اختیاری)" dir="ltr" inputMode="numeric"/></>}
  {!admin&&<input value={phone} onChange={e=>setPhone(e.target.value)} placeholder="شماره موبایل" dir="ltr"/>}
  <input value={password} onChange={e=>setPassword(e.target.value)} placeholder="رمز عبور (حداقل ۴ کاراکتر)" type="password" dir="ltr"/>{!admin&&register&&<input value={password2} onChange={e=>setPassword2(e.target.value)} placeholder="تکرار رمز عبور" type="password" dir="ltr"/>}
  {error&&<div className="error">{error}</div>}
  <button className="btn primary" disabled={busy} onClick={submit}>{busy?'در حال بررسی…':admin?'ورود به پنل':register?'ثبت‌نام':'ورود'}</button>
  {!admin&&<button className="textBtn" onClick={()=>{setRegister(!register);setError('')}}>{register?'حساب دارم؛ ورود':'حساب ندارم؛ ثبت‌نام'}</button>}
  {admin&&<small>حساب مدیر: <b>admin</b> / <b>44953322</b></small>}
  {!admin&&<><button className="textBtn" onClick={()=>{localStorage.setItem('mwoodi_guest','1');router.push('/products')}}>ادامه به‌عنوان میهمان</button><Link href="/login?admin=1" className="textBtn">ورود مدیر</Link></>}
 </div></section>
}
