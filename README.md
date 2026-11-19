# Core Gas WordPress theme

Custom theme for **Core Gas Private Limited**: OGRA-licensed downstream natural gas marketing and sales.

It needs no page builder and no ACF. Every piece of content and every global option is edited from WordPress post types.

## Install on staging

1. Install the theme in one of two ways:
   - **Upload a zip:** download the repository zip, then go to **Appearance → Themes → Add New → Upload Theme**.
   - **Connect the repo:** link this repository with a Git deploy tool such as WP Pusher, the host's Git integration, or a GitHub Action that deploys over SSH/SFTP. Point it at `wp-content/themes/coregas`.
2. Activate **Core Gas**. On first activation, the theme creates starter content, all taken from the company profile:
   - **Pages:** Home, About Us, Gas Supply, Regulation, Contact Us, News, Legal notice and Accessibility.
   - **Front page and posts page settings.**
   - **Homepage sections:** Hero and About.
   - **Content items:** four supply services, the OGRA licence and six group companies.
   - **Menus:** Main, four footer columns and Legal, already assigned to their locations.

   The routine runs only once and never overwrites pages or menus that already exist.
3. Go to **Settings → Permalinks** and click **Save** once to refresh the URLs.

## Where to edit things

The **Dashboard** opens with three widgets:

- **Edit your website:** a button for every page, plus links to all the global tools.
- **Website checklist:** what is still missing before launch, with "Fix" links.
- **Latest enquiries:** the newest form submissions.

| What | Where |
| --- | --- |
| Everything on a page (banner, every section, text, photos, buttons) | **Pages → edit the page**. Sections are panels: open, edit, move up/down, hide, delete, or **Add a section** |
| SEO title, description and share image of a page, post or item | Its **SEO & sharing** box |
| Logo, favicon, default photos | **Theme Settings → Brand** |
| All colours (brand, backgrounds, header, footer, buttons) | **Theme Settings → Colours** |
| Fonts, text size, heading size and capitals, content width, spacing, photo corners, hero height | **Theme Settings → Typography & layout** |
| Header button and behaviour | **Theme Settings → Header** |
| Footer text, copyright, logo, social icons, phone/email | **Theme Settings → Footer** (link columns: **Appearance → Menus**) |
| Social profiles | **Theme Settings → Social media** |
| Address, phone, email, hours, map; enquiry-form labels, options, messages, recipient | **Theme Settings → Contact & form** |
| Default description, share image, verification codes | **Theme Settings → SEO & sharing** |
| List-page banners, the 404 page, all small website wording | **Theme Settings → Pages & wording** |
| GA4, Tag Manager, Meta Pixel, custom code, custom CSS | **Theme Settings → Analytics & code** |
| Flame, headline animation, pulsing dots, smooth scrolling | **Theme Settings → Motion** |
| Form submissions | **Enquiries** (also emailed) |
| Supply services, licences, industries, group companies, news | Their own menu items (photo = Featured image) |

### Backup, restore and safety

- **Theme Settings → Backup & restore:** download all settings as a file, import a file (e.g. staging → live), and restore any of the last 20 versions. A version is kept automatically on every save, import and restore.
- Every Theme Settings section has **Reset this section to defaults**.
- **Theme Settings → Security:** XML-RPC off, WordPress version hidden, built-in code editor locked (all on by default).
- The WordPress login page uses the Core Gas logo and colours (Theme Settings → Brand).
- CSS/JS files are versioned by their modification time, so browsers fetch every deployed change automatically.

### Section types

| Type | Shows |
| --- | --- |
| Hero | Live flame, black-and-white photo, headline, intro, button, key facts |
| About block | Statement, intro, image, pillars, parent-group block |
| Image and text | Photo on one side, heading + text + buttons on the other |
| List of items | Services / licences / industries / companies / news as alternating rows, a steps strip, or a licence record panel |
| Steps strip | Your own steps, side by side with photos and arrows |
| Columns | 2–4 text columns, optional photo and link each |
| Photo gallery | Grid of photos with captions |
| Questions & answers | Click-to-open questions |
| Contact details + form | Address/phone/email, enquiry form, optional map |
| Video or map | YouTube/Vimeo link or Google Maps embed |
| Text | Heading + rich text |
| Call to action | Heading, text, button |

Each section also has **Space above and below**, **Show on (all / computers only / phones only)**, **Anchor ID** and an optional **CSS class**.

## Brand rules built in

- The official logo (`assets/img/core-gas-logo.webp`) is used exactly as supplied. On the dark header it sits on a white plate. **Theme Settings → Brand** can turn the plate off or replace the logo.
- The default colours are the exact colours sampled from the logo:
  - **Maroon** `#48050B`
  - **Warm grey** `#544742`
  - **Crimson** `#800024`
  - **Red** `#D20600`
  - **Orange** `#E36F00`
  - **Amber** `#F1AD00`
  - **Yellow** `#FCEF00`
- The default heading font is Barlow (NVIDIA style). **Theme Settings → Typography** offers big-brand equivalents and body fonts.
- No decorative numbering is used anywhere (01, 02 and so on).

## Development

```
coregas-theme/
├── functions.php            loads inc/*
├── inc/
│   ├── fields.php           meta-box field framework (text, colour, image, repeater…)
│   ├── schemas.php          every editable field, per post type
│   ├── post-types.php       custom post types
│   ├── settings.php         Theme Settings singleton + coregas_setting()
│   ├── head.php             CSS variables, GA4/GTM, custom code
│   ├── sections.php         Page Sections rendering
│   ├── contact-form.php     native enquiry form (nonce + honeypot)
│   ├── seed.php             starter content on activation
│   └── setup.php / fonts.php / template-tags.php
├── template-parts/          sections/*, card, banner, listing
├── page-templates/contact.php
└── assets/                  css, js (flame shader, motion), img
```

- **Reading values:**
  - Global settings: `coregas_setting( 'key' )`.
  - A field on any post: `coregas_meta( $post_id, 'key' )`.
  - Both fall back to the schema default.
- **Adding a field:** add it to `inc/schemas.php`. The meta box, sanitisation and saving are automatic.
- **Script versions:** the motion libraries come from pinned CDN versions (GSAP 3.12.5, Lenis 1.1.13).

## Before launch

- **Photos:** replace the placeholder Unsplash photos in the Hero and About sections by uploading your own images in the Page Sections.
- **Contact details:** add them in **Theme Settings → Contact** and check that the form email reaches the recipient. Staging may need an SMTP plugin.
- **Legal pages:** fill in Legal notice, Privacy and Accessibility.
- **Analytics:** add the GA4 or GTM ID.
