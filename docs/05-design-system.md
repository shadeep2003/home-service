# Shared design system

Source: `public/css/app.css`. Reuse tokens and Blade components across pages.

| Token | Value / purpose |
| --- | --- |
| Navy | `#123047`: headings, illustration roof, dark feature areas |
| Teal | `#087f82`: primary actions and secondary visual colour |
| Orange | `#f4a361`: warm illustration accent and focus ring |
| Background | `#f8f9f6`: soft off-white |
| Card | `#ffffff` |
| Muted text | `#526575` |
| Border | `#d4dfe4` |
| Danger | `#b42318`: existing form errors |
| Radius | 18px cards, 11px buttons, 9px form fields |
| Shadow | `0 14px 40px #1230470a` |
| Typography | System sans-serif; no external font dependency |

The content width is 1180px. Layouts adapt for tablet at 980px, mobile at 700px and narrow screens at 380px. Shared service cards, section headings, buttons, navbar, footer, workflow and CTA are anonymous Blade components. The hero uses a local inline SVG illustration with CSS scenery. Primary actions are teal/white; secondary actions are white/navy with a border. Hover effects use subtle movement and shadows; reduced-motion preferences disable transitions. Existing authentication input and dashboard styling remains available.

Keyboard provisions include skip navigation, visible focus, native menu/FAQ disclosures and active-page labels. Browser visual validation and formal contrast/accessibility assessment remain pending.

Future status tokens and feature-specific components should be introduced with the corresponding functionality. Status labels must include text, rather than relying on colour alone. See `07-public-frontend.md` for architecture and verification notes.
