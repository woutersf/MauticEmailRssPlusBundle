# MauticEmailRssPlusBundle

Advanced RSS feed management plugin for Mautic emails.

## Features

- **RSS Feed Management**: Create and manage multiple RSS feeds with custom fields
- **Template System**: Create reusable templates for RSS items
- **Admin Interface**: Full CRUD interface for feeds and templates
- **Token Support**: Configurable token support for email integration
- **Integration Support**: Proper Mautic integration with enabled/disabled toggle
- **RSS Icon**: Uses the same RSS feed icon as the standard RSS import plugin

## Installation

1. The plugin is located in `plugins/MauticEmailRssPlusBundle/`
2. Clear Mautic cache: `php -d memory_limit=512M bin/console cache:clear`
3. Install/Update plugins: `php -d memory_limit=512M bin/console mautic:plugins:reload`
4. Run database migrations: `php -d memory_limit=512M bin/console doctrine:migrations:migrate --no-interaction`
5. Enable the plugin in Mautic Settings > Plugins > RSS Plus
6. Configure the integration in Settings > Plugins > RSS Plus > Features and enable the plugin

## Database Tables

### rssplus_feeds
- `id`: Auto-increment primary key
- `name`: Feed name (string 255)
- `machine_name`: Machine name for identification (string 255)
- `rss_url`: RSS feed URL (string 500)
- `rss_fields`: RSS fields to extract (text)
- `button`: Show button in editor (0/1)
- `token`: Enable token (0/1)
- `created_at`: Creation timestamp
- `created_by`: Creator user ID
- `updated_by`: Last update user ID

### rssplus_templates
- `id`: Auto-increment primary key
- `name`: Template name (string 255)
- `content`: Template HTML content (text)
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp
- `updated_by`: Last update user ID

## Integration Configuration

The plugin is configured as a Mautic integration. To access the configuration:

1. Go to **Settings** (gear icon in top right)
2. Click **Plugins**
3. Find **RSS Plus** in the plugin list
4. Click to open the plugin configuration
5. In the **Features** tab, you'll find:
   - **Enable RSS Plus Features**: Toggle to enable/disable the plugin features

The admin menu and features will only be visible when the integration is enabled.

## Usage

After installation and enabling the integration, you'll find a new "RSS Plus" menu in the admin area with:

1. **Feeds** (`/s/rssplus/feeds/`): Manage RSS feeds
   - Add new feeds with URL and field configuration
   - Configure button and token options
   - Edit and delete existing feeds

2. **Templates** (`/s/rssplus/templates/`): Manage templates
   - Create HTML templates with token placeholders
   - Available tokens: {title}, {link}, {description}, {category}, {pubDate}, {media}
   - Edit and delete existing templates

## Template Tokens

Templates support the following tokens that will be replaced with RSS feed values:
- `{title}` - Article title
- `{link}` - Article URL
- `{description}` - Article description
- `{category}` - Article category
- `{pubDate}` - Publication date
- `{media}` - Media/image URL

## Version

1.0.0

## Author

Frederik Wouters
