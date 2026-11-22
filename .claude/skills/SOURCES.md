# Skill sources

Third-party skills vendored into this repository. Do not edit them in place: update by copying a newer version from the source and updating this table.

| Skill | Source | Commit | Date | License | Used for |
|---|---|---|---|---|---|
| wp-plugin-development | [WordPress/agent-skills](https://github.com/WordPress/agent-skills) | `7374e6f1c913b3b02b25465d266d9c0e0a04fc96` | 2026-11-05 | GPL-2.0-or-later | Plugin architecture, hooks, security |
| wp-block-themes | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | Block theme, `theme.json`, templates, style variations |
| wp-block-development | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | Dynamic blocks (`block.json`, `render.php`) |
| wp-patterns | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | Block patterns, starter pages, template parts |
| wp-interactivity-api | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | Front-end interactivity without a React app |
| wp-rest-api | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | REST routes, schema and permissions for Agents |
| wp-phpstan | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | Static analysis |
| wp-wpcli-and-ops | WordPress/agent-skills | same | 2026-11-05 | GPL-2.0-or-later | WP-CLI, seed data, operations |
| frontend-design | [anthropics/skills](https://github.com/anthropics/skills) | `e4bb5d960d87126ff3b3a8af1a104b7db67560ba` | 2026-11-16 | Apache-2.0 (`LICENSE.txt` in folder) | Visual identity and UI direction |
| theme-factory | anthropics/skills | same | 2026-11-16 | Apache-2.0 (`LICENSE.txt` in folder) | Applying the identity to promotional material |
| webapp-testing | anthropics/skills | same | 2026-11-16 | Apache-2.0 (`LICENSE.txt` in folder) | Playwright checks of the local site |

The WordPress skills license is in `LICENSE-wordpress-agent-skills.txt`.

Project skills (written for this project, editable):

| Skill | Used for |
|---|---|
| euteajudo-identity | Applying the "Luz acesa" identity (tokens, Night/Day modes, logo, tone of voice) to the theme, interface copy, emails, social posts and video. Created 2026-11-22 with `skill-creator`; no eval set (subjective output). |

Not vendored, available globally in Claude Code: `skill-creator` (used to create project skills).
