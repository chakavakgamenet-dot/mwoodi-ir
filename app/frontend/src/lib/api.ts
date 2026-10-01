// Browser requests go through the Next.js same-origin proxy. This avoids CORS failures
// between mwoodi-web.onrender.com and mwoodi-api.onrender.com.
const API = '/api/v1';

function token() {
  if (typeof window === 'undefined') return '';
  return localStorage.getItem('mwoodi_token') || '';
}
export function saveToken(t:string){ if(typeof window!=='undefined') localStorage.setItem('mwoodi_token',t); }
export function clearToken(){ if(typeof window!=='undefined') localStorage.removeItem('mwoodi_token'); }

export async function api<T>(path:string, options:RequestInit={}):Promise<T>{
  const headers = new Headers(options.headers || {});
  if (options.body && !headers.has('Content-Type')) headers.set('Content-Type','application/json');
  headers.set('Accept','application/json');
  const t=token(); if(t) headers.set('Authorization',`Bearer ${t}`);
  const res=await fetch(`${API}${path}`,{...options,headers,cache:'no-store'});
  if(!res.ok){
    let message = `درخواست ناموفق بود (${res.status})`; 
    try {
      const body:any=await res.json();
      message=body.message || Object.values(body.errors||{}).flat().join(' ') || message;
    } catch {}
    throw new Error(message);
  }
  return res.json() as Promise<T>;
}

export async function apiNetworkSafe<T>(path:string, options:RequestInit={}):Promise<T>{
  try {
    return await api<T>(path, options);
  } catch (e:any) {
    const msg = String(e?.message || '');
    if (msg.includes('Failed to fetch') || msg.includes('NetworkError') || msg.includes('Load failed')) {
      throw new Error('ارتباط با سرور برقرار نشد. لطفاً چند ثانیه بعد دوباره تلاش کنید.');
    }
    throw e;
  }
}
export {API};
