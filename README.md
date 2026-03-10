# Portfolio GitHub Activity Pulse

A Drupal module that fetches, merges, and color-codes GitHub contribution heatmaps from multiple accounts (Personal and Work). It features a "Bloggify" style grid with support for private contributions via the official GitHub GraphQL API.

## Features

- **Dual Account Support**: Separate configurations for Personal and Work GitHub accounts.
- **Unified Heatmap**: Merges activity from both accounts into a single, cohesive calendar.
- **Smart Color Coding**: 
  - **Green**: Personal contributions.
  - **Orange**: Work contributions.
  - **Split Squares**: A diagonal gradient (half green/half orange) for days where you contributed to both.
- **Private Contribution Support**: Uses GitHub Personal Access Tokens (PAT) to fetch private activity that public proxies can't see.
- **No External JS**: Rendered server-side via Twig and styled with CSS for maximum performance and SEO.
- **Responsive Grid**: Includes a scrollable container for mobile compatibility.

## Installation

1. Upload the `portfolio_github` folder to your Drupal site's `modules/custom/` directory.
2. Enable the module via the Drupal UI (**Extend**) or using Drush:
   ```bash
   drush en portfolio_github
   ```

## Configuration

### 1. Global Settings
Navigate to **Configuration > Services > GitHub Activity Settings** (`/admin/config/services/portfolio-github`):

- **Personal Account**: Enter your personal GitHub username and a Personal Access Token (PAT).
- **Work Account**: Enter your work GitHub username and a PAT.
- **Tokens**: Tokens should have at least the `read:user` scope. If no token is provided, the module will only fetch public data via a proxy.

### 2. Placing the Block
1. Navigate to **Structure > Block layout**.
2. Click **Place block** in your desired region (e.g., Content or a custom region).
3. Search for **GitHub Activity Pulse**.
4. (Optional) You can override usernames specifically for this block instance in the block configuration.

## Customization

### Styling
The module includes its own CSS library. To customize the colors or grid size, you can override the styles in your theme's CSS file or modify:
`modules/custom/portfolio_github/css/github-activity.css`

### Template
To change the HTML structure, copy the following file to your theme's `templates` folder:
`modules/custom/portfolio_github/templates/portfolio-github-block.html.twig`

## Requirements
- Drupal 10 or 11
- Guzzle (included with Drupal Core)
- GitHub Personal Access Token (recommended for private activity)

## Author
**Andrei Sandu**
- GitHub: [sanduandrei](https://github.com/sanduandrei)
