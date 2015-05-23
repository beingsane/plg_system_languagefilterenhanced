# Language Filter Enhanced for Joomla

Joomla offers a great *Language Filter* plugin which allows you to build a multilingual site. 
However, it has one functionality which might be unwanted and that is that when you switch to a certain language other than
the default, all links will be for that language, untill you switch back to the default language. Effectively, this
forces you to translate ALL of your content, or remain with a duplicate content issue.

This plugin solves that issue. But because the issue might not be entirely clear, here's a description of the problem.

## Problem description
### A English site with only 1 page in French
Let's say you have an English site: All of your content is written in English. Now you add another language, say French. But
you don't want to translate the entire site into French, you only care about a few pages that need to be in French. For
example, your site contains a blog, an About-Us page and a contact page. Now you translate that contact page into French,
while all other content is still in English only. Next, you make sure setup Joomla properly for multiple languages.

Once you're done, you are left with a challenge: All your content might be configured to match *All Languages* (indicated
within the database with a wildcard `*`). Except of course the contact page, which is duplicated: One contact page
specifically in the language *English* and one contact page specifically in the language *French*. You might say this works
fine.

### Duplicate content because of the language URL prefix
Now, when your browse to the blog in English the URL might be `/en/blog` or just `/blog`. However, when you go the blog in
French the URL is `/fr/blog`. But the content is exactly the same as your English blog! This is duplicate content. So
instead of going from the French contact-page to the French blog (which does not exist), you actually want to go the English
blog (`/en/blog`).

You might be tempted to force the blog to be English, by changing the language to *English*. This will not work either. When
you are on the French contact-page, the blog will not be available in French, so any links to it will be hidden by Joomla.

### Solution: Remove the language URL prefix for global content
The solution is simple: Just remove any language URL prefix for items that are actually global content. So instead of
interpreting the `*` wildcard as something indicating that the current language *French* fits, we interpret the wildcard 
so that it says that the default language *English* fits.

## Current workings
First of all, it hooks in the SEF routing mechanism, so that the URLs of items that have their language set to `*`
is not the current language, but the default language instead.

Second - and this is kind of hackish - this plugin unsets the `language` cookie that is set by the Joomla language system.
This cookie will be set to the current language, which again will force all `*` items to be in that language as well.
