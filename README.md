# Related By Taxonomy

A Statamic addon that returns related entries using common taxonomy terms (tags).

## Features

Very basic for now. 

## How to Install

Will have to look into that. Never done a composer package... yet. 

But, most likely it will look like this:

``` bash
composer require niktwenty3/related-by-taxonomy
```

## How to Use

For now its very limited but the basic usage is: 

```
{{ relbytaxonomy taxonomies="tags|categories|products|authors|features" modifiers="1.2|1.5|.5|.5|1" }}
    {{ related_entries }}
        <h2>
            <a class=" " href="{{ url }}">{{ title }}</a>
        </h2>
    {{ /related_entries }}
{{ /relbytaxonomy }}
```

The taxonomies parameters accepts the taxonomies that will be searched for common terms with the current entry.

The modifiers parameter accepts the modifier that will be multiplied to produce the relationship score.

## How it works

1. The tag will search all posts in the current collection for common tags and will add a score on each entry depending on how many it found. 
2. The score is calculated by multiplying the modifier for the taxonomy by 1. 
3. The list created is sorted descinding by higher score and then sliced to keep only the top 4 results which are then sorted by most recent. 
4. The tag returns the results in the {{ related_entries }} variable as an object array in wich each item is a Statamic entry.