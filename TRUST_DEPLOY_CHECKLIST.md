# Mivonta Trust And Deployment Checklist

Use this checklist before exposing the site publicly.

## Brand And Identity

- Use `Mivonta` consistently across public pages, login flows, dashboard UI, email templates, and support content.
- Publish a real business identity page with company name, jurisdiction, and operating address.
- Use a domain-based support email such as `support@mivonta.com` and keep it consistent across pages and emails.
- Remove placeholder or legacy references to other brands from user-facing content.

## Legal And Policy Content

- Keep Terms and Privacy text aligned with actual product behavior.
- Remove claims about processors, retention periods, or compliance obligations unless they are true and documented.
- Avoid support instructions that require customers to complete financial actions through chat apps.
- Document how users can contact support, request deletion, and raise disputes.
- Review all age, jurisdiction, dispute, and retention language with legal counsel before production use.

## Security And Production Setup

- Serve all public traffic over HTTPS only.
- Keep JSON data stores and internal logs inaccessible from the web.
- Disable local-only testing conveniences before deployment.
- Review reset, activation, and admin flows for abuse resistance.
- Rotate secrets and move production credentials out of committed local override files.
- Confirm session cookies are `HttpOnly`, `SameSite`, and secure in production.
- Verify CSRF protection on all state-changing forms and API routes.

## Product And UX Trust Signals

- Keep homepage messaging factual and specific about what the portal does.
- Avoid exaggerated financial claims, unverifiable testimonials, or invented service guarantees.
- Provide clear support, privacy, and terms links in the footer of public pages.
- Ensure support labels match the actual action, for example do not label a chat link as email support.
- Make account review, withdrawal review, and transfer status messaging clear and consistent.

## Operational Checks

- Test login, reset, dashboard, send, add money, withdraw, and admin flows in a clean session.
- Confirm that outgoing emails use `Mivonta` branding and correct reply addresses.
- Search for leftover `Mivonta` references in runtime files before each release.
- Review audit logs and error logs for leaked secrets or personal data.
- Re-check public pages in browser after each branding or legal-content update.
