# oEmbed Github
A plugin for WordPress that provides oEmbed services for GitHub.

## Installation

### Install via your WordPress website

1. Visit ‘Plugins > Add New’
2. Search for ‘oembed-github’
3. Activate oEmbed Github from your Plugins page.

### Manual Installation

To manually install oEmbed Github clone this repository and place it's content within a folder named `oembed-github` within your `wp-content/plugins` directory. Once you are finished, visit your Plugins Page and activate the oEmbed Github plugin.

**Final Directory Structure**
```
.
-- wp-content
---- oembed-github
------ oembed-github.php
------ LICENSE
```

## Supported Content

* Profiles
* Repositories
* Commits
* Pull Requests
* Issues
* Gists

## Supported URL's

All URL's support using both `https` and `http`, as well as with `www.` or without `www.` (Gists excluded)

* `https://github.com/{username}`
* `https://github.com/{username}/{repository}`
* `https://github.com/{username}/{repository}/commit/{commit}`
* `https://github.com/{username}/{repository}/pull/{pull}/commits/{commit}`
* `https://github.com/{username}/{repository}/pull/{pull}`
* `https://github.com/{username}/{repository}/issues/{issue}`
* `https://gist.github.com/{gist}`

#### Patterns Used

GitHub
```
#^((http(s|)):\/\/|)(www.|)github.com(\/)(([a-z0-9-?&%_=]*))((\/([a-z0-9-?&%_=]*))|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)(\/([a-z0-9-?&%_=]*)|)#i
```

Gist
```
#^((http(s|)):\/\/|)gist.github.com\/([a-z0-9-?&%_=]*)\/([a-z0-9-?&%_=]*)(\/|)#i
```

## GitHub API

In order to avoid the GitHub API rate limit, you can provide your GitHub application `Client ID` and `Client Token` in the `oEmbed Github` options area within your Wordpress Admin dashboard.

## Theming

You can customize the theme of all embeded content from the `oEmbed Github` options area within your Wordpress Admin dashboard.

## Screen Shots

<p align="center">
  <img src="https://i.imgur.com/TN9esTJ.png" alt="Profile">
</p>
<p align="center">
<img src="https://i.imgur.com/8N6GqXR.png" alt="Repository">
</p>
<p align="center">
<img src="https://i.imgur.com/KC4Mgda.png" alt="Commit">
</p>
<p align="center">
<img src="https://i.imgur.com/IvF4dLK.png" alt="Commit 2">
</p>
<p align="center">
<img src="https://i.imgur.com/FJjYS4w.png" alt="Pull Request">
</p>
<p align="center">
<img src="https://i.imgur.com/g7kZ2vH.png" alt="Issue">
</p>
<p align="center">
<img src="https://i.imgur.com/HETw7DJ.png" alt="Gist">
</p>

## Attribution

This plugin makes use of [gist-embed](https://github.com/blairvanderhoof/gist-embed) by [Blair Vanderhoof](https://github.com/blairvanderhoof)

## License

oEmbed Github is licensed under GPLv3 (See [LICENSE](https://github.com/nathan-fiscaletti/oembed-github/blob/master/LICENSE))
