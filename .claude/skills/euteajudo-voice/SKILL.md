---
name: euteajudo-voice
description: The welcoming voice of EuTeAjudo.org ("voz do acolhimento"). Use it to write or review any Portuguese text people read on EuTeAjudo.org - screens, buttons, hints, errors, empty states, emails, the consent, the About, Terms and Privacy pages - so that every line welcomes the person first. Use it with euteajudo-identity whenever interface copy changes, and whenever someone says a text sounds cold, bureaucratic, technical or pejorative.
---

# Voz do acolhimento

## Who writes

Write as someone who has spent years in active-listening shifts (the kind of volunteer who answers the phone at 3 a.m.) and who also writes interfaces. That person knows that whoever arrives here is tired, ashamed or afraid, and is deciding in seconds whether this place is safe. Every word either opens that door or closes it.

## What to put first

1. **The person, never the system.** Talk about what the person can do and feel ("Você pode escrever do seu jeito"), not about how the site works inside.
2. **Care before rules.** Rules (age, consent, removed data) exist to protect the person; say so in that order: the protection, then the rule.
3. **Anonymity as protection, not as a number.** The Alias ("Amigo 48213") protects the person. Never say or imply that the person "is just a number". Say that they don't need to give their name, that here they are called Amigo, with a number that is only theirs, so nobody finds out who they are.
4. **The care is the highlight, the AI is information.** The welcome, the attention and the safety lead. The AI is stated plainly and honestly where ADR 0002 requires it (sign-up, write page, conversation, home), once per place, never as the headline or the first thing the person reads, never hidden. Present it as what it is: attention written with care, available at any hour, following rules of respect.
5. **Errors guide without blame.** Say what to do next, in the person's favour. Never "inválido", "erro", "falhou". List every problem at once.
6. **Waiting is held, not announced.** "Pode levar algumas horas. A gente avisa por e-mail" rather than service-level language.

## How it sounds

- "Você", short sentences, plain Portuguese, warm but not sugary.
- No promises ("vai ficar tudo bem"), no slogans, no exclamation marks, no emojis, no diminutives that infantilise, no clinical words (paciente, caso, atendimento, usuário, solicitação, cadastro de dados).
- No technical words: dados pessoais removidos -> "tiramos telefones e e-mails para proteger você"; navegador, sessão, link only when there is no plainer way.
- Buttons say what happens, keep the same name across the flow ("Começar a escrever", "Enviar pedido", "Encerrar pedido").
- Domain words from `GLOSSARY.md`: Pedido, Resposta, Amigo, Apelido, Encerramento. Use them, but the sentence around them must be human.

## Limits the voice must keep

- **Consent (LGPD arts. 11 and 33)**: the sign-up must still ask, in a visible checkbox, for consent to (a) the text being read by an artificial intelligence and (b) it being processed by services outside Brazil, and must link the Terms and the Privacy policy. It can be warm and short; it cannot be vague or hidden.
- **AI disclosure (ADR 0002)**: present on the four places above, clearly saying the replies are written by an artificial intelligence.
- **Crisis**: the 188 (CVV) stays reachable on the home and in the footer. Say it as care ("Se estiver difícil agora..."), never as a warning.
- **18+**: still stated at sign-up; the under-18 screen welcomes and points to Pode Falar and CVV without making the person feel rejected.
- **Truth**: never promise a human, a deadline, or something the site does not do.

## Process

Interface strings live in English in the code and in Portuguese in `plugin/languages/euteajudo-pt_BR.po` (ADR 0006). When proposing changes, give the new Portuguese text (the `msgstr`); when the meaning changes, also give a new English source. Content pages are in `plugin/content/pt_BR/`. `npm run texts:review` lists every current text by screen in `docs/texts-review.md`.
