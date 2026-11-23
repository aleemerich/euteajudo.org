---
name: euteajudo-identity
description: Apply the EuTeAjudo.org visual identity "Luz acesa" (colors, Night/Day modes, Atkinson Hyperlegible Next, lit-window logo, tone of voice) to anything that faces people. Use this skill whenever you create or change theme.json, block styles, patterns, templates, front-end CSS, interface copy (buttons, errors, empty states, emails), social media posts, video covers, subtitles or any promotional material for EuTeAjudo.org, even if the request does not mention "identity", "brand" or "design".
---

# EuTeAjudo.org identity: "Luz acesa"

People write here when something weighs on them, often late at night. The identity is a lit window on a dark street: someone is awake and available. Every choice serves calm, legibility and anonymity. When in doubt, choose the quieter option.

## Sources of truth (read before producing anything)

- `design/tokens.json`: every color, size, radius and spacing value. Never invent a hex value or a size; if a value is missing, add it to the tokens first and say so.
- `design/identity.md`: concept, logo rules, contrast table, tone of voice, social and video rules (in Portuguese).
- `design/logo/`: logo files. Use them as they are; do not redraw the logo.
- `design/fonts/web/`: self-hosted woff2 files.
- `design/proposals/b-light-on.html`: the approved reference screens (Night and Day) for layout and component look.

Paths are relative to the `platform/` repository root.

## Core rules and why

1. **One warm light.** Amber `#F2B13B` (`color.brand.lamp`) marks the logo's lit pane, buttons, the active tab and "new reply". Nothing else uses amber, and nothing else glows. If everything shines, the window stops meaning "someone is here".
2. **Two modes, same roles.** Night (default) and Day use the same token names under `color.night.*` and `color.day.*`. Pick the mode in this order: the visitor's explicit choice (stored only in the browser), then the device preference, then Night. Implement with CSS custom properties that switch per mode, so blocks never branch on the mode.
3. **Contrast is checked, not assumed.** Text must reach WCAG AA (4.5:1). In Day mode amber is only 1.7:1 against the background, so the focus ring becomes night green and buttons get a 1px `#B7790F` border. Check new color pairs before using them.
4. **One typeface, self-hosted.** Atkinson Hyperlegible Next only: 400 body, 600 labels, 700 headings, 17px body with 1.55 line height. Load it from the theme, never from Google Fonts or any CDN: a third-party font request leaks the visitor's IP, which breaks the anonymity promise.
5. **Calm motion.** The only automatic animation is the lit window turning on once on the home page. Respect `prefers-reduced-motion`. Motion that answers a person's action (opening, confirming) is fine.
6. **Touch first.** Minimum 48px touch targets, bottom tab bar on phones (Escrever, Meus pedidos, Conta), reading column of 720px on wide screens.
7. **No decoration that pretends to be information.** No uppercase eyebrow labels, no numbered markers unless the content is a real sequence, no decorative shadows, no stock photos, no illustrations of people.

## Tone of voice (Portuguese copy)

The full voice, with what to put first and the limits it must keep, is the `euteajudo-voice` skill; use it for any text. In short:

Low voice at night: short sentences, nothing that rushes. Address the person as "você"; use the Alias when it fits ("Oi, Amigo 48213").

- Buttons say what happens, and the same action keeps the same name across the flow: "Começar a escrever", "Enviar pedido", "Encerrar pedido".
- Waiting: "Seu pedido chegou e vai ser lido com atenção. A resposta pode levar algumas horas; a gente avisa por e-mail."
- Errors explain and guide, without apologizing: "O texto está vazio. Escreva algumas linhas para enviar."
- Empty states invite: "Nada por aqui ainda. Quando quiser escrever, é só começar."
- Never: promises ("vai ficar tudo bem"), motivational slogans, exclamation marks, emojis, technical terms, uppercase labels.
- Domain words come from `GLOSSARY.md` (Pedido, Resposta, Amigo, Apelido, Encerramento). Interface source strings are written in English and translated to pt_BR, per ADR 0006; the Portuguese above is the target wording.

## Applying it

**WordPress theme (theme.json, styles, patterns, blocks):** map tokens to `settings.color.palette`, `settings.typography.fontFamilies` (with `fontFace` pointing to the theme's woff2 files), `fontSizes`, `spacing.spacingSizes` and custom properties for radii. Palette values point to CSS variables that switch between Night and Day. Use the `wp-block-themes`, `wp-patterns` and `wp-block-development` skills for the WordPress mechanics; this skill decides the look and words.

**Emails:** plain layout, Night text on white is not required; use Day colors for readability in mail clients. Neutral sender and subject ("EuTeAjudo: você tem uma nova mensagem"), never any content of a Pedido.

**Social posts and video:** Night background with Luar text by default; one idea per piece; `design/logo/social-avatar.svg` as profile image; subtitles always on, Atkinson Hyperlegible Next 600 on a Night band at 85% opacity, window symbol in the bottom right corner. Never use real Pedido texts or testimonials, even anonymized. Templates live in `design/social-kit/`.

## Before you finish

Check the result against this list and fix what fails:
- Every color and size comes from `tokens.json`.
- Amber appears only where rule 1 allows.
- Both Night and Day were checked (screenshot or reasoning), including focus rings.
- No external font or asset requests.
- Copy follows the tone rules and the glossary.
