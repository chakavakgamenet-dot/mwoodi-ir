# Migration from v37

## Keep
- visual language
- product presentation patterns
- cart interaction ideas
- admin information architecture where useful
- copy/style references

## Remove as source of truth
- localStorage customer records
- localStorage orders
- localStorage auth state
- localStorage inventory
- client-side payment success

## Migration order
1. Extract reusable UI into React components.
2. Create product/category API.
3. Create auth and customer API.
4. Move cart to server; keep guest cart token.
5. Implement transactional checkout.
6. Implement payment adapter + callback verification.
7. Connect admin.
8. Add audit logs, backups, rate limits and monitoring.
9. Replace prototype localStorage paths with API calls.
10. Run staging checkout/payment tests before production.
