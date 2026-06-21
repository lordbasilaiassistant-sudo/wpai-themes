// Generate docs/gallery.json (consumed by the gallery UI) plus one WordPress
// Playground blueprint per item (docs/playground/<slug>.json) that installs and
// activates the theme/plugin straight from its zip on the deployed site.
import { writeFileSync } from 'node:fs';
import { join } from 'node:path';
import {
  listThemes, listPlugins, DOCS_DIR, SITE_URL, ensureDir,
  parseThemeHeader, parsePluginHeader,
} from './lib.mjs';

function tagList(v) {
  return v ? v.split(',').map((s) => s.trim()).filter(Boolean) : [];
}

const playgroundDir = join(DOCS_DIR, 'playground');
ensureDir(playgroundDir);

// A real demo site so live previews feel inhabited: posts with featured images,
// categories, an About page, a populated nav menu, comments, and an author.
const IMG = (name) => `${SITE_URL}demo/${name}.jpg`;

const DEMO = {
  site: { name: 'Northwind', tagline: 'Ideas worth shipping.' },
  author: { name: 'Mara Ellison', bio: 'Designer and engineer writing about craft, code, and calm.' },
  pages: [
    {
      title: 'About',
      image: IMG('slate'),
      content:
        '<p>Northwind is a small publication about craft, code, and calm — written for people who build things and care how they feel to use.</p>' +
        '<p>It is run by <strong>Mara Ellison</strong>, a designer and engineer who has spent a decade shipping software and learning, slowly, that less is usually more. These are field notes from that work: half lessons, half reminders to herself.</p>' +
        '<p>No trackers, no pop-ups, no newsletter to escape. Just writing, and the occasional good idea worth keeping.</p>',
    },
  ],
  posts: [
    {
      title: 'Designing for calm',
      category: 'Design',
      image: IMG('dusk'),
      excerpt: 'Good software feels quiet. Here is how to build interfaces that get out of the way.',
      content:
        '<p>Good software feels quiet. It does the thing you asked, then steps out of the way and leaves room for your own thoughts.</p>' +
        '<h2>Less, but better</h2>' +
        '<p>Every element on a page is a small request for attention. The craft is deciding which requests are worth making — and deleting the rest without mercy.</p>' +
        '<blockquote>Simplicity is not the absence of detail. It is detail spent only where it counts.</blockquote>' +
        '<p>When in doubt, remove. The page that remains is almost always stronger, and the people using it can finally hear themselves think.</p>',
    },
    {
      title: 'The real cost of a slow website',
      category: 'Engineering',
      image: IMG('tide'),
      excerpt: 'Speed is a feeling before it is a number. Here is where the milliseconds hide.',
      content:
        '<p>Speed is a feature you feel before you can name it. A page that loads instantly feels trustworthy; a slow one feels broken even when everything technically works.</p>' +
        '<h2>Where the milliseconds go</h2>' +
        '<ul><li>Web fonts that block the first paint</li><li>Hero images three times larger than their container</li><li>Scripts that run before anyone has scrolled a pixel</li></ul>' +
        '<p>Trim each one and the whole experience lightens. Your visitors will not know why it feels good. They will just stay.</p>',
    },
    {
      title: 'Notes on shipping small',
      category: 'Craft',
      image: IMG('ember'),
      excerpt: 'The smallest version of an idea that still helps someone is the best place to start.',
      content:
        '<p>The smallest version of an idea that still helps someone is usually the right place to start. You learn more from one real user than from a month of speculation in a quiet room.</p>' +
        '<h2>Ship, then listen</h2>' +
        '<p>Release early, watch closely, and let the next step reveal itself. Momentum compounds in ways planning never can.</p>' +
        '<p>Perfect is a story we tell ourselves to avoid the discomfort of being seen. Ship the honest version instead.</p>',
    },
    {
      title: 'A field guide to better mornings',
      category: 'Life',
      image: IMG('grove'),
      excerpt: 'Protect the first hour. The rest of the day tends to follow it.',
      content:
        '<p>The first hour sets the temperature for everything after it. Guard it like it matters, because it does.</p>' +
        '<h2>Three small rituals</h2>' +
        '<ul><li>Light before screens — open a window before you open a tab</li><li>One page of writing, badly, before the inbox</li><li>A walk short enough that you will actually take it</li></ul>' +
        '<p>None of this is new. That is the point. The boring habits are the ones that hold.</p>',
    },
    {
      title: 'What old maps teach us about design',
      category: 'Ideas',
      image: IMG('sand'),
      excerpt: 'The best maps leave things out on purpose. So should the best products.',
      content:
        '<p>A map that showed everything would be useless — and exactly the size of the world. Every great map is an argument about what matters.</p>' +
        '<h2>The art of leaving out</h2>' +
        '<p>Designers are mapmakers. We choose a scale, pick a projection, and decide which roads earn a line. The omissions are the design.</p>' +
        '<blockquote>The map is not the territory — but a good one tells you where to walk next.</blockquote>',
    },
  ],
  comments: [
    { author: 'Jonah Reed', content: 'This put words to something I have felt for years but could not name. Sharing it with my whole team.' },
    { author: 'Priya Nair', content: '"Detail spent only where it counts" — I am stealing that for our design review on Monday.' },
    { author: 'Sam Okafor', content: 'Came for the typography, stayed for the philosophy. More of this, please.' },
  ],
  menuCategories: ['Design', 'Engineering'],
};

const DEMO_PHP =
  "<?php\n" +
  "require_once '/wordpress/wp-load.php';\n" +
  "require_once '/wordpress/wp-admin/includes/media.php';\n" +
  "require_once '/wordpress/wp-admin/includes/file.php';\n" +
  "require_once '/wordpress/wp-admin/includes/image.php';\n" +
  "$d = json_decode(<<<'WPAIJSON'\n" +
  JSON.stringify(DEMO) + "\n" +
  "WPAIJSON, true);\n" +
  "if ( ! is_array( $d ) ) { return; }\n" +
  "function wpai_img( $url, $post_id = 0 ) {\n" +
  "  if ( ! $url ) { return 0; }\n" +
  "  $tmp = download_url( $url );\n" +
  "  if ( is_wp_error( $tmp ) ) { return 0; }\n" +
  "  $file = array( 'name' => basename( parse_url( $url, PHP_URL_PATH ) ), 'tmp_name' => $tmp );\n" +
  "  $id = media_handle_sideload( $file, $post_id );\n" +
  "  if ( is_wp_error( $id ) ) { @unlink( $tmp ); return 0; }\n" +
  "  return (int) $id;\n" +
  "}\n" +
  "function wpai_cat( $name ) {\n" +
  "  $t = term_exists( $name, 'category' );\n" +
  "  if ( ! $t ) { $t = wp_insert_term( $name, 'category' ); }\n" +
  "  if ( is_wp_error( $t ) ) { return 0; }\n" +
  "  return (int) ( is_array( $t ) ? $t['term_id'] : $t );\n" +
  "}\n" +
  "update_option( 'blogname', $d['site']['name'] );\n" +
  "update_option( 'blogdescription', $d['site']['tagline'] );\n" +
  "wp_update_user( array( 'ID' => 1, 'display_name' => $d['author']['name'], 'nickname' => $d['author']['name'], 'description' => $d['author']['bio'] ) );\n" +
  "foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $def ) {\n" +
  "  $ex = get_page_by_path( $def[0], OBJECT, $def[1] );\n" +
  "  if ( $ex ) { wp_delete_post( $ex->ID, true ); }\n" +
  "}\n" +
  "$page_ids = array();\n" +
  "foreach ( $d['pages'] as $pg ) {\n" +
  "  $id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => $pg['title'], 'post_content' => $pg['content'], 'post_status' => 'publish', 'post_author' => 1 ) );\n" +
  "  if ( $id && ! empty( $pg['image'] ) ) { $a = wpai_img( $pg['image'], $id ); if ( $a ) { set_post_thumbnail( $id, $a ); } }\n" +
  "  if ( $id ) { $page_ids[ $pg['title'] ] = $id; }\n" +
  "}\n" +
  "$cat_ids = array();\n" +
  "$first = 0; $i = 0;\n" +
  "foreach ( $d['posts'] as $post ) {\n" +
  "  $i++;\n" +
  "  $pid = wp_insert_post( array( 'post_title' => $post['title'], 'post_content' => $post['content'], 'post_excerpt' => isset( $post['excerpt'] ) ? $post['excerpt'] : '', 'post_status' => 'publish', 'post_author' => 1, 'post_date' => gmdate( 'Y-m-d H:i:s', time() - $i * 86400 ) ) );\n" +
  "  if ( ! $pid ) { continue; }\n" +
  "  if ( ! $first ) { $first = $pid; }\n" +
  "  if ( ! empty( $post['category'] ) ) { $c = wpai_cat( $post['category'] ); if ( $c ) { wp_set_post_categories( $pid, array( $c ) ); $cat_ids[ $post['category'] ] = $c; } }\n" +
  "  if ( ! empty( $post['image'] ) ) { $a = wpai_img( $post['image'], $pid ); if ( $a ) { set_post_thumbnail( $pid, $a ); } }\n" +
  "}\n" +
  "if ( $first && ! empty( $d['comments'] ) ) {\n" +
  "  $h = 1;\n" +
  "  foreach ( $d['comments'] as $cm ) {\n" +
  "    wp_insert_comment( array( 'comment_post_ID' => $first, 'comment_author' => $cm['author'], 'comment_content' => $cm['content'], 'comment_approved' => 1, 'comment_date' => gmdate( 'Y-m-d H:i:s', time() - $h * 3600 ) ) );\n" +
  "    $h++;\n" +
  "  }\n" +
  "}\n" +
  "$menu_id = wp_create_nav_menu( 'Demo Menu' );\n" +
  "if ( ! is_wp_error( $menu_id ) ) {\n" +
  "  wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Home', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) );\n" +
  "  if ( ! empty( $page_ids['About'] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'About', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['About'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); }\n" +
  "  foreach ( $d['menuCategories'] as $cn ) { if ( ! empty( $cat_ids[ $cn ] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $cn, 'menu-item-object' => 'category', 'menu-item-object-id' => $cat_ids[ $cn ], 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ) ); } }\n" +
  "  $regs = get_registered_nav_menus();\n" +
  "  $locs = get_theme_mod( 'nav_menu_locations' );\n" +
  "  if ( ! is_array( $locs ) ) { $locs = array(); }\n" +
  "  if ( is_array( $regs ) && $regs ) { foreach ( array_keys( $regs ) as $loc ) { $locs[ $loc ] = $menu_id; } } else { $locs['primary'] = $menu_id; }\n" +
  "  set_theme_mod( 'nav_menu_locations', $locs );\n" +
  "}\n";

function demoSteps() {
  return [{ step: 'runPHP', code: DEMO_PHP }];
}

function writeBlueprint(slug, kind) {
  const zipUrl = `${SITE_URL}downloads/${slug}.zip`;
  const install = kind === 'theme'
    ? { step: 'installTheme', themeZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } }
    : { step: 'installPlugin', pluginZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } };
  const blueprint = {
    $schema: 'https://playground.wordpress.net/blueprint-schema.json',
    landingPage: '/',
    preferredVersions: { php: '8.0', wp: 'latest' },
    features: { networking: true },
    steps: [install, ...demoSteps()],
  };
  writeFileSync(join(playgroundDir, `${slug}.json`), JSON.stringify(blueprint, null, 2));
}

function themeEntry(t) {
  const h = parseThemeHeader(t);
  writeBlueprint(t.slug, 'theme');
  return {
    slug: t.slug, type: 'theme',
    name: h['Theme Name'] || t.slug,
    description: h['Description'] || '',
    version: h['Version'] || '1.0.0',
    author: h['Author'] || 'WPAI Themes',
    tags: tagList(h['Tags']),
    requiresWp: h['Requires at least'] || '',
    requiresPhp: h['Requires PHP'] || '',
    license: h['License'] || 'GPL-2.0-or-later',
    screenshot: `screenshots/${t.slug}.png`,
    zip: `downloads/${t.slug}.zip`,
    blueprint: `playground/${t.slug}.json`,
  };
}

function pluginEntry(p) {
  const h = parsePluginHeader(p);
  writeBlueprint(p.slug, 'plugin');
  return {
    slug: p.slug, type: 'plugin',
    name: h['Plugin Name'] || p.slug,
    description: h['Description'] || '',
    version: h['Version'] || '1.0.0',
    author: h['Author'] || 'WPAI Themes',
    tags: [],
    requiresPhp: h['Requires PHP'] || '',
    license: h['License'] || 'GPL-2.0-or-later',
    screenshot: `screenshots/${p.slug}.png`,
    zip: `downloads/${p.slug}.zip`,
    blueprint: `playground/${p.slug}.json`,
  };
}

const themes = listThemes().map(themeEntry);
const plugins = listPlugins().map(pluginEntry);

const data = {
  generatedAt: new Date().toISOString(),
  site: SITE_URL,
  counts: { themes: themes.length, plugins: plugins.length },
  themes,
  plugins,
};

writeFileSync(join(DOCS_DIR, 'gallery.json'), JSON.stringify(data, null, 2));

// robots.txt + sitemap.xml for search engines.
writeFileSync(
  join(DOCS_DIR, 'robots.txt'),
  `User-agent: *\nAllow: /\n\nSitemap: ${SITE_URL}sitemap.xml\n`
);
writeFileSync(
  join(DOCS_DIR, 'sitemap.xml'),
  '<?xml version="1.0" encoding="UTF-8"?>\n' +
  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
  `  <url>\n    <loc>${SITE_URL}</loc>\n    <lastmod>${data.generatedAt.slice(0, 10)}</lastmod>\n` +
  '    <changefreq>weekly</changefreq>\n    <priority>1.0</priority>\n  </url>\n' +
  '</urlset>\n'
);

console.log(`✓ gallery.json + sitemap.xml + robots.txt — ${themes.length} theme(s), ${plugins.length} plugin(s)`);
