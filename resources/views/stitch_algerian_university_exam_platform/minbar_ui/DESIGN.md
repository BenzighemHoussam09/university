```markdown
# Design System Specification: Academic Integrity & Editorial Depth

## 1. Overview & Creative North Star
The Creative North Star for this design system is **"The Digital Atheneum."** 

Moving away from the sterile, cold nature of traditional "portal" software, this system draws inspiration from high-end editorial journals and prestigious academic archives. We aim to replace the "utility-only" feel of exam platforms with a sense of quiet authority and focused calm. 

Instead of a rigid, boxed-in grid, we utilize **Intentional Asymmetry** and **Tonal Depth**. By breaking the "template" look with overlapping elements and generous white space, we create a signature experience that feels custom-built for the Algerian academic elite. The layout is designed to respect the flow of Arabic (RTL) script, treating the text as the primary visual hero rather than a secondary element.

---

## 2. Colors & Surface Philosophy
The palette is rooted in a deep, scholarly green (`primary: #00342b`), evoking the heritage of Algerian academic excellence and the security of a high-stakes environment.

### The "No-Line" Rule
To achieve a premium, editorial feel, **1px solid borders are strictly prohibited for sectioning.** Physical boundaries must be defined solely through background color shifts or subtle tonal transitions.
*   **Implementation:** If a content block needs to be distinguished, place a `surface-container-low` card atop a `surface` background.

### Surface Hierarchy & Nesting
Think of the UI as physical layers of fine paper or frosted glass. Use the following tiers to define importance:
*   **Canvas:** `surface` (#f8f9fa) is your base.
*   **Secondary Zones:** Use `surface-container-low` (#f3f4f5) for sidebar or background navigation areas.
*   **Interactive Cards:** Use `surface-container-lowest` (#ffffff) to make cards "pop" against a slightly darker background.
*   **Callouts:** Use `primary-container` (#004d40) for high-importance notices.

### The "Glass & Gradient" Rule
To prevent a "flat" or "generic" appearance, use semi-transparent surface colors with a `backdrop-filter: blur(12px)` for floating navigation bars or modal headers. 
*   **Signature Textures:** Apply a subtle linear gradient to main CTAs transitioning from `primary` (#00342b) to `primary-container` (#004d40) at a 135-degree angle. This adds a "soul" to the action buttons that flat colors lack.

---

## 3. Typography: The Editorial Voice
We utilize a dual-font strategy to balance international modernism with regional heritage.

*   **Display & Headlines (Public Sans):** Used for large numbers (timer, grades) and section titles. It provides a crisp, authoritative structure.
*   **Body & Arabic Context (Inter / Custom Cairo):** While Inter serves the Latin characters, Cairo/Tajawal must be used for the Arabic script to ensure perfect legibility in RTL.

**The Hierarchy of Intent:**
*   **Display-LG (3.5rem):** For monumental moments (e.g., "Exam Complete").
*   **Headline-MD (1.75rem):** For major section headings.
*   **Body-LG (1rem):** The default for exam questions. It must be legible and airy.
*   **Label-MD (0.75rem):** All-caps (for Latin) or bolded (for Arabic) for metadata like "Question 1 of 50."

---

## 4. Elevation & Depth
In this system, depth is a matter of light and shadow, not lines.

*   **The Layering Principle:** Stack `surface-container-lowest` on top of `surface-container` to create a natural lift.
*   **Ambient Shadows:** For floating elements like "Finish Exam" modals, use an extra-diffused shadow: `box-shadow: 0 20px 40px rgba(25, 28, 29, 0.06)`. Note the low opacity; it should feel like a soft glow of shadow, not a dark smudge.
*   **The "Ghost Border" Fallback:** If a border is essential for accessibility, use `outline-variant` (#bfc9c4) at 20% opacity. Never use a 100% opaque border.
*   **Glassmorphism:** Use `surface_variant` at 70% opacity with a blur effect for persistent status bars (e.g., the student's ID and remaining time) to make the interface feel modern and integrated.

---

## 5. Components

### Buttons & Interaction
*   **Primary:** Rounded `lg` (1rem). Background is the signature gradient. Text is `on-primary`.
*   **Secondary:** No background. Use a "Ghost Border" and `primary` colored text.
*   **Focus State:** A 3px offset ring using `surface_tint`. Accessibility is non-negotiable.

### Cards & Lists
*   **Constraint:** **Prohibit divider lines.** Use 24px or 32px of vertical white space to separate list items.
*   **Exam Cards:** Use `surface-container-high` for the hover state to provide a tactile "press-in" feel.
*   **Rounding:** Apply `md` (0.75rem) to standard cards and `xl` (1.5rem) to large hero containers.

### Input Fields
*   **Style:** Soft-filled backgrounds (`surface-container-highest`) rather than outlined boxes.
*   **RTL Alignment:** Icons (like search or padlock) must flip position in Arabic mode. The "Focus" state should animate a 2px bottom-bar in `primary` color.

### Custom Component: The Progress "Aura"
Instead of a standard horizontal progress bar, use a subtle, large-scale gradient background in the header that expands as the student completes questions, providing a non-intrusive, "ambient" sense of progress.

---

## 6. Do’s and Don’ts

### Do
*   **Do** prioritize RTL flow. Ensure that the "Next" button is on the left for Arabic and the right for English.
*   **Do** use asymmetrical margins. A wider margin on the "start" side of the text creates a sophisticated editorial look.
*   **Do** use `primary-fixed-dim` for disabled states instead of generic grey to keep the brand's "soul" present even in inactive elements.

### Don’t
*   **Don’t use pure black (#000000).** Use `on-surface` (#191c1d) for text to maintain a high-end, softer contrast.
*   **Don’t use standard 1px dividers.** If you must separate content, use a background color shift or a wide 8px gap.
*   **Don’t overcrowd.** This system thrives on "Breathing Room." If in doubt, increase the padding.
*   **Don’t use sharp corners.** Everything must feel "Welcoming and Official," which requires the specified `roundedness` scale.```