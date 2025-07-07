RiverLea Documentation
======================
v0.1 - 7 July 2025

# Getting Started

## Introduction

RiverLea Theme Framework separates CiviCRM's visual/UI CSS from structural CSS, using CSS variables. Installing it loads four 'Streams' and a fifth empty Stream to build from. Streams, similar to Drupal Subthemes, are mostly CSS variables:
 - Minetta, named after the river that runs under Greenwich, NYC. It is based on Civi's default 'Greenwich' theme.
 - Walbrook, named after the river that runs under Shoreditch, London. It is based on Shoreditch/TheIsland theme.
 - Hackney, named after the river that runs under Finsbury Park, based on Finsbury Park theme.
 - Thames, named after the river that runs close to Artful Robot HQ, based on their Aah theme.

## Setup

- For installations of CiviCRM 6.0 and later, take the Nav menu > Administer > Customize Data and Screens > Display Preferences, and select which subtheme/stream you want near the bottom.
- For installations of CiviCRM 5.80 to 6.0, go to extensions settings, enable the RiverLea extension and follow the instructions above.
- For installations of CiviCRM 5.75 to 5.79, you can install as a custom extension from https://lab.civicrm.org/extensions/riverlea - via ftp, cli, git, or ssh. Make sure you delete the extension from your custom extensions folder if/when you upgrade to 5.80 and later.
- For installations of CiviCRM before 5.75 there will be some issues with RiverLea in some parts of CiviCRM and it's not recommended to use it.

## Structure

### Core variables
A list of all base variables used on all streams is at `core/css/_variables.css`.

### Stream/subtheme directories
Each ‘stream’ or subtheme directory must contain a further directory `css` with a `_variables.css`file and a `_dark.css` if darkmode is supported. Variables in this `_variables.css` file will overrule any variables in the core list above. The subtheme can also include fonts, images and other CSS files, which can be loaded from the `_variables.css` file as an import.

### Core directory
Contains CSS files in:
- In the **core/css** directory are theme files marked with an underscore:
  - core/css/_base.css – resets, basic type, colours, links, positioning
  - core/css/_bootstrap.css – a Bootstrap subset
  - core/css/_cms.css – resets and fixes specific to different CMSs
  - core/css/_core.css - links to the UI components in the components directory:
  - core/css/_fixes.css - CSS that’s necessary *for now* but one day could go.
  - core/css/_variables.css - a list of all base variables
- in the **components** directory are reusable  UI elements, such as `_accordions` or `_tables.css`;
- civicrm.css - the core theme css file which loads the other files
- other files here without underscore (`admin.css`, `api4-explorer.css`, `contactSummary.css` etc) overrides civicrm's CSS core directory with files of the same name that are called by templates and only load in certain parts of Civi. E.g. `dashboard.css` loads on the CiviCRM main dashboard, and no-where else.
- three directories: `org.civicrm.afform-ang` for Afform output, `org.civicrm.afform_admin-ang` for FormBuilder and `org.civicrm.search_kit-css` for SearchKit replace css files in core Civi extensions.

## Customising

Adding a customiser is on the roadmap, with a working prototype, but until its issues are resolved customising can be done through one of the three following methods:

### 1. Add CSS variables to your parent theme

For instance, to give all contribution page buttons rounded corners, you could add to your CMS theme:

```
--crm-btn-radius: 2rem;
```

Exploring the _variables.css file will give you idea of how much can be overwritten.

### 2. Create a subtheme 'stream'

1. Inside the `/streams/` directory is an example stream called `empty`. Duplicate this and rename it the name of your stream.
2. In riverlea.php add a theme array to the function `riverlea_civicrm_themes(&$themes)`.
3. Edit `/streams/[streamname]/css/_variables.css` with your custom css variables. You can link to other CSS files, fonts or images in this file - inside the stream.

E.g. to add a stream called "Vimur", you would name the directory 'vimur', and add the following:

```
 function riverlea_civicrm_themes(&$themes) {
  $themes['vimur'] = array(
    'ext' => 'riverlea',
    'title' => 'Riverlea: Vimur',
    'prefix' => 'streams/vimur/',
  );
  $themes['minetta'] = array(
    'ext' => 'riverlea',
    'title' => 'Riverlea: Minetta (~Greenwich)',
    'prefix' => 'streams/minetta/',
  );
  …
 }
```

Use of the [ThemeTest extension](https://lab.civicrm.org/extensions/themetest) is recommended to more quickly identify which CSS variables match which UI element, and test multiple variations for each.

IMPORTANT NOTE: Every time you upgrade RiverLea you will need to add your Stream again. This is obviously less than ideal, so for produciton you may prefer option 3:

### 3. Create a subtheme extension

NB: this approach has had very limited testing

1. Create a theme extension using Civix, following the [instructions in the CiviCRM Developer Guide](https://docs.civicrm.org/dev/en/latest/framework/theme/).
2. Create a subtheme of RiverLea using the instructions in **2. Create a subtheme 'stream'** above.
3. Copy the subtheme into the root of your new theme extenion.
4. Edit its main php file, enable both extensions and select your stream.
E.g. for a stream called 'styx', with a theme extension called 'ocean', then in ocean.php you would write:

```
function ocean_civicrm_themes(&$themes) {
  $themes['styx'] = array(
    'ext' => 'ocean',
    'title' => 'River Styx',
    'prefix' => 'styx/',
    'search_order' => array('styx', '_riverlea_core_', '_fallback_'),
  );
}
```

If you want to include your own styles in addition to variables, you can include a civicrm.css in your subtheme in the same directory as _variables.css. You'll need to include the Riverlea civicrm.css in your civicrm.css: ```@import url(/path/to/your/ext/riverlea/core/css/civicrm.css);```.

# Core CSS

# Components

## Accordions

### General

**--crm-expand-radius: var(--crm-roundness);**
Border-radius for all four corners of details and summary elements. When details is expanded the bottom two corner of the summary are set to 0 to give a straight line.Constraint: this needs to be a single value, there can’t be different values for each corner.

**--crm-expand-gap: var(--crm-xs2) 0 0;**
The external margin for details elements.

### Icon

**--crm-expand-icon: "\f0da";**
A unicode value for a FontAwesome icon, see [fontawesome.com/v5/search](https://fontawesome.com/v5/search). Constraints: Unicode value, prefixed with backslash, inside double quotes

**--crm-expand-icon-color: var(--crm-c-text);**
Icon colour.

**--crm-expand-icon-spacing: var(--crm-m);**
Space between icon and summary text.

**--crm-expand-transform: rotate(90deg);**
Transform to icon on click.

**--crm-expand-transition: transform .3s;**
Animation transition on icon on click

### Style one (.crm-accordion-bold)

**--crm-expand-header-bg: var(--crm-c-secondary);**
Summary background colour.

**--crm-expand-header-bg-active: var(--crm-c-gray-900);**
Summary hover and expanded background colour.

**--crm-expand-header-color: var(--crm-c-secondary-text);**
Summary text colour.

**--crm-expand-header-padding: var(--crm-s) var(--crm-m);**
Summary padding (1-4 values)

**--crm-expand-header-weight: bold;**
Summary font-weight.

**--crm-expand-header-font: var(--crm-font-bold);**
Summary font-family.

**--crm-expand-header-border: var(--crm-c-divider);**
Summary border (short notation, ie ‘1px solid green’)

**--crm-expand-header-border-width: 0 0 1px 0;**
To hide a border on only some sides of the summary, you can set the border to zero (and keep the border width for the sides you want). The order is top, right, bottom, left. So to show no border on left or right, and 2px top / 4px bottom, you would write ‘2px 0 4px 0’.

**--crm-expand-border: var(--crm-c-divider);**
Border for the details content (`<div class=”crm-accordion-body”>`). NB to avoid duplicate borders, there isn’t a variable to apply a border to the entire details element - instead a border is applied to both the summary and div.crm-accordion-body, which together provides a border for the entire accordion.

**--crm-expand-border-width: 0 1px 1px 1px;**
Border width for the details content region, follows the same pattern as `--crm-expand-header-border-width` above.

**--crm-expand-body-bg: unset;**
Background colour for the details content region.

**--crm-expand-body-box-shadow: unset;**
Box-shadow for the details content region.

**--crm-expand-body-padding: var(--crm-padding-reg);**
Padding for the details content region.

### Style two (.crm-accordion-light)

**--crm-expand2-header-bg: unset;**
Summary background colour.

**--crm-expand2-header-bg-active: var(--crm-c-background-2);**
Summary hover and expanded background colour.

**--crm-expand2-header-weight: normal;**
Summary font-weight.

**--crm-expand2-header-font: unset;**
Summary font-family.

**--crm-expand2-header-color: var(--crm-c-text);**
Summary text colour

**--crm-expand2-header-border: unset;**
Summary border

**--crm-expand2-header-border-width: unset;**
Summary border width (see --crm-expand-header-border-width above).

**--crm-expand2-header-padding: var(--crm-s) var(--crm-m);**
Summary padding.

**--crm-expand2-border: unset;**
Content body border (see --crm-expand-border above).

**--crm-expand2-border-width: unset;**
Content body border width (see --crm-expand-header-border-width above).

**--crm-expand2-body-bg: unset;**
Background colour for the details content region.

**--crm-expand2-body-padding: var(--crm-s);**
Padding for the details content region.

## Alerts
## Buttons
## Dialogs
## Dropdowns
## Forms
## FormBuilder & SearchKit
## Icons
## Nav
## Pages
## Tables
## Tabs
