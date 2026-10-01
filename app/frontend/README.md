# MWoodi Storefront
Next.js + TypeScript storefront implementing the final MWoodi visual direction from v37.

## Routes
- `/` homepage
- `/products` catalog/search/filter
- `/products/[id]` product detail
- `/cart` cart
- `/login` authentication UI
- `/account` customer account
- `/admin` admin dashboard shell

Browser API calls use same-origin `/api/v1`; Next.js proxies them server-side to the Render API using `BACKEND_ORIGIN`. Bearer tokens are stored client-side.
