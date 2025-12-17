# WordPress.org Assets

This directory contains assets for the WordPress.org plugin directory page. These files should **NOT** be included in the plugin ZIP file.

## Contents

### Banners
- `banner-1544x500.png` - High resolution banner (1544x500px)
- `banner-772x250.png` - Standard banner (772x250px)  
- `banner-1200x300.png` - Alternative banner size (1200x300px)

### Screenshots
- `screenshot-1.png` - Active countdown bar on frontend
- `screenshot-2.png` - Alternative content after countdown expires
- `screenshot-3.png` - General & Schedule settings panel
- `screenshot-4.png` - Content & Countdown settings panel
- `screenshot-5.png` - Action on Finish settings panel
- `screenshot-6.png` - Appearance settings panel

## How to Upload to WordPress.org

After your plugin is approved, you'll need to upload these assets separately via SVN:

### Step 1: Checkout the Assets Directory

```bash
svn co https://plugins.svn.wordpress.org/antikton-topbar-countdown/assets
```

### Step 2: Copy Files

```bash
cd assets
cp /path/to/.wordpress-org-assets/* .
```

### Step 3: Add and Commit

```bash
svn add *.png
svn ci -m "Adding plugin assets (banners and screenshots)"
```

## More Information

- [How Plugin Assets Work](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/)
- [WordPress.org Plugin Handbook](https://developer.wordpress.org/plugins/)
