import './globals.css';
import Link from 'next/link';

export const metadata = { title: 'MWoodi | هنر چوب برای خانه', description: 'محصولات چوبی MWoodi با دقت و ظرافت برای خانه شما.' };

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return <html lang="fa" dir="rtl"><body>
    <header className="header">
      <Link href="/" className="logo"><span className="mark">♨</span><span><b>MWoodi</b><small>WOOD · CRAFT · HOME</small></span></Link>
      <nav><Link href="/">خانه</Link><Link href="/products">محصولات</Link><Link href="/#about">درباره ما</Link></nav>
      <div className="actions"><Link href="/login" className="icon">♙</Link><Link href="/cart" className="cart">سبد خرید</Link></div>
    </header>
    <main>{children}</main>
    <footer><b>MWoodi</b><span>چوب، وقتی با عشق ساخته می‌شود، ماندگار می‌شود.</span><span>© 1405 MWoodi</span></footer>
  </body></html>
}
