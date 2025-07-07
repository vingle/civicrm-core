# RiverLea Theme Framework

This Framework separates CiviCRM's visual/UI CSS from structural CSS, using CSS variables. Installing it provides you with four subthemes or 'Streams' which are entirely created with CSS variables (other than Thames, which uses a little bit of CSS as well):
 - Minetta, named after the river that runs under Greenwich, NYC. It is based on Civi's default 'Greenwich' theme.
 - Walbrook, named after the river that runs under Shoreditch, London. It is based on Shoreditch/TheIsland theme.
 - Hackney, named after the river that runs under Finsbury Park, based on Finsbury Park theme.
 - Thames, named after the river that runs close to Artful Robot HQ, based on their Aah theme.
 You can chose between these subthemes via Display Settings, where you can also set dark-mode preferences.

 The extension is licensed under [AGPL-3.0](LICENSE.txt).

 ## Use in Front-End CiviCRM

**USE WITH CAUTION AND TESTING** While RiverLea has been widely tested in the backend of CiviCRM, be very careful to use in front-end. Given the wide number of themes and scenarios for front-end pages, for existing sites we recommend only applying it to an existing web front-end after comprehensive testing on a dev site.

Overwriting CSS variables for the front is straightforward (they can be nested within `.crm-container.crm-public` and there's a number of front-end specific variables, prefixed `--crm-f-`), but **testing is essential**.

## [Docs](docs.md)
Covers installation, structure and installation.

## [Changelog](CHANGELOG.md)

- 1.4.x-6.2.alpha - ongoing fixes/changes against 6.2
- 1.3.x-6.0.alpha - Contrast ratios, responsive and Front-end changes, including new variables, plus fixes (see changelog)
- 1.2.x-5.81 - Regression fixes against 5.81 RC
- 1.1 (5.80) - Release for packaging with CiviCRM core, v5.80
- 1.0 - **Release candidate**, with ongoing testing and fixes.
- 0.10 - **Adds fourth stream**. Thames (Aah), as well as extensive fixes & adjustments.
- 0.9 - **Overwrites civi core CSS**. 5.75 only - overwrites core css like SearchKit & FormBuilder with extensive work on both. D7 Garland support.
- 0.8 - **Front-end layouts**. Front-end support for each stream.
- 0.7 - **Dark-mode**. Dark-mode working across all three streams.
- 0.6 - **Adds third stream** Hackney Brook (Finsbury Park).
- 0.5 - **Extensive UI and accessibility fixes** following testing in/around CiviCamp Hamburg.
- 0.4 - **CSS files restructure** core and stream directories, version numbering of variables files with new variables.
- 0.3 - **Two streams, 6 CMS setups tested:** adds Minetta and Walbrook streams. Backdrop, D7 (Seven), D9 (Claro + Seven), Joomla 4, Standalone & WordPress.
- 0.2 - **Establishes structure**, adds Bootstrap3, components - accordion.
- 0.1 - **Proof-of-concept**, basic variables.

### Version numbering
RiverLea has its own version number and confusingly this has changed a few times while we figured out the best approach. It currently takes the form `[River Lea version]-[CiviCRM version built on]`.

This means there might be simultaneously versions `1.2.1-5.81.beta` and `1.3.0-5.82.alpha`.

Please ignore previous numbering patterns.

## Troubleshooting
- Unless you really need it (e.g. applying an urgent fix, or running a test), delete the custom/ext version of RiverLea, once you are on CiviCRM 5.80 or later.
- After removing the custom/ext RiverLea directory, the civicrm/ext version should load automatically. It may appear to be enabled, but normally you will need to disable and re-enable it before RiverLea streams appear in Display Settings.
