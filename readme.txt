=== DP Easy Social Share ===
Contributors: divyangprajapati
Donate link: https://www.paypal.com/paypalme/PrajapatiDivyang
Tags: social, social share, social icons, social media, sharing
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 7.1
Stable tag: 2.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight, customizable social sharing plugin for WordPress that adds social icons to your posts, pages and custom post types.

== Description ==

DP Easy Social Share is a simple and lightweight social sharing plugin for WordPress. With an easy-to-use interface and customizable options, you can add social sharing buttons to your posts, pages and custom post types effortlessly. Enhance your content's reach by enabling your visitors to share your posts on their favorite social networks.

== Features ==

* **Easy-to-Use Interface:** Configure your social sharing settings through an intuitive admin settings page.
* **Customizable Icons:** Choose from a selection of icons via checkboxes including Facebook, X, LinkedIn, WhatsApp, Reddit, Pinterest, and Email.
* **Inline Icons:** Set the social icons to appear above or below your content.
* **Floating Sidebar:** Show a floating share sidebar fixed to the left or right edge of the screen, independent of your inline icons.
* **Multi-Post Type Support:** Specify which post types display the social share icons, with separate settings for inline and floating placement.
* **Lightweight & Fast:** Designed to add social sharing functionality without slowing down your site.
* **Responsive Design:** Social icons adjust seamlessly for mobile and desktop viewing.

== Installation ==

1. Upload the `dp-easy-social-share` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the "DP Easy Social Share" menu to manage the plugin settings

== Frequently Asked Questions ==

= How do I enable social sharing icons? =
After installing and activating the plugin, go to the plugin settings page in your WordPress admin area. From there, you can choose which icons to display on your site.
= Does this plugin support custom post types? =
Yes. You can specify the post types in the settings page where you want the icons to appear.
= Can I show a floating share sidebar as well as inline icons? =
Yes. The Floating Sidebar tab lets you enable a separate floating share bar, fixed to the left or right edge of the screen, with its own post type targeting independent of the inline icons.
= Can I customize the icons or URLs? =
Yes. The plugin now supports filters:
- `dpessr_social_share_url_{$network}`: Customize the share URL per social network.
- `dpessr_social_share_icons`: Modify the entire array of icon data (label, icon, url, etc).

**Examples:**

*Change WhatsApp share URL:*
<code>
<?php
add_filter('dpessr_social_share_url_whatsapp', function($url, $post_url, $post_title) {
    return 'https://wa.me/?text=' . urlencode($post_title . ' ' . $post_url);
}, 10, 3);
?>
</code>

*Modify icon data (e.g., add a custom network):*
<code>
<?php
add_filter('dpessr_social_share_icons', function($icons) {
    $icons['custom'] = [
        'name' => 'Custom',
        'icon' => '<svg>...</svg>',
        'url'  => 'https://custom.com/share?u={url}',
    ];
    return $icons;
});
?>
</code>

== Screenshots ==

1. **General Tab:** Select which social icons appear across your site.
2. **Inline Buttons Tab:** Enable inline icons, choose the post types, and set the display position (above or below content).
3. **Floating Sidebar Tab:** Enable the floating share sidebar, choose the post types, and set the screen edge (left or right).
4. **Inline Icons on the Frontend:** A post displaying the inline social share icons in the configured position.
5. **Floating Sidebar on the Frontend:** A post displaying the floating share sidebar fixed to the configured screen edge.

== External Services ==

This plugin uses Font Awesome to provide social media icons for sharing posts and pages. Font Awesome is a third-party icon library that is included to enhance the visual presentation of social icons.

** Font Awesome **

* **What the service is and what it is used for:**  
  The plugin uses Font Awesome to display SVG icons for various social media platforms such as Facebook, X (formerly Twitter), LinkedIn, WhatsApp, Reddit, Pinterest, Email and more.

* **What data is sent and when:**  
  No personal data is sent from the user's website to Font Awesome. The icons are embedded locally in the plugin, and no external calls to Font Awesome servers are made.

* **Terms of Use and Privacy Policy:**  
  - [Font Awesome Terms of Use](https://fontawesome.com/license/free)  
  - [Font Awesome Privacy Policy](https://fontawesome.com/privacy)

== Changelog ==

= 2.0.0 =
* Added: Floating share sidebar, fixed to the left or right edge of the screen, with its own enable/disable, post type targeting, and position setting, independent of inline icons.
* Added: Subtle slide-in and fade-in entrance animation for the floating sidebar (respects the visitor's reduced-motion preference).
* Added: Tab-style rounded edge on floating icons (rounded on the outer edge only, flush against the screen edge) instead of full circles.
* Added: Time-delayed, dismissible "please rate us" admin notice, shown only after a week of active use, with rate now / already rated / maybe later options that persist your choice.
* Added: Brand logo in the admin settings header.
* Tested for compatibility with WordPress 7.0.

= 1.1.3 =
* Updated compatibility to WordPress 7.0

= 1.1.2 =
* Improved share URL encoding with rawurlencode for better standards compliance.
* Escaped raw URL and title values before filtering for enhanced safety.
* Tested compatibility with WordPress 6.9.

= 1.1.1 =
* Improved SVG icon handling and sanitization for security.
* Enhanced accessibility and responsiveness of icons.
* Minor code and style improvements.

= 1.1.0 =
* Added filter hooks for customizing share URLs and icon data.

= 1.0.1 =
* Updated compatibility to WordPress 6.8

= 1.0.0 =
* Initial release of DP Easy Social Share.
* Basic social sharing functionality.

== Upgrade Notice ==

= Unreleased =
Adds a floating share sidebar, an admin rating prompt. See changelog for details.

= 1.1.3 =
Tested for compatibility with WordPress 7.0.

= 1.1.2 =
Improved share URL handling, extended filter support, and tested with WordPress 6.9.

= 1.1.1 =
Improved SVG icon handling, accessibility enhancements, and minor fixes.

= 1.1.0 =
Adds developer filter hooks.

= 1.0.1 =
Tested for compatibility with WordPress 6.8.

== License ==

DP Easy Social Share is released under the GPLv3 license. This means you are free to use, modify, and distribute the software as long as you comply with the license terms. See [https://www.gnu.org/licenses/gpl-3.0.txt](https://www.gnu.org/licenses/gpl-3.0.txt) for more details.