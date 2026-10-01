import './globals.css';
import StoreHeader from './StoreHeader';
export const metadata = { title: 'MWoodi | هنر چوب برای خانه', description: 'محصولات چوبی MWoodi با دقت و ظرافت برای خانه شما.' };
export default function RootLayout({ children }: { children: React.ReactNode }) {
 return <html lang="fa" dir="rtl"><body><StoreHeader/><main>{children}</main><footer><b>MWoodi</b><span>چوب، وقتی با عشق ساخته می‌شود، ماندگار می‌شود.</span><span>© 2026 MWoodi</span></footer></body></html>;
}
