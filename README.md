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
{{ relbytaxonomy limit="4" taxonomies="tags|categories|products|authors|features" modifiers="1.2|1.5|.5|.5|1" stopwords="with,and,the,use,how,to,as,a,what,you,should,know" }}
    {{ related_entries }}
        <h2>
            <a class=" " href="{{ url }}">{{ title }}</a>
        </h2>
    {{ /related_entries }}
{{ /relbytaxonomy }}
```

**Parameters**

`taxonomies`: accepts the taxonomies that will be searched for common terms with the current entry.

`modifiers`: parameter accepts the modifier that will be multiplied to produce the relationship score per taxonomy.

`limit`: how many results to return.

`stopwords`: these are words that are removed when doing title comparison. 

## How it works

1. The tag will search all posts in the current collection for common tags and will add a score on each entry depending on how many it found. 
2. The tag will also compare the words between the titles and produce a score (0 - 100) which represents the percentage common unique words found. 
3. The final score is calculated by multiplying the modifier for the taxonomy by 1, and, by adding the title score divided by 10 (0 - 10). 
4. The list created is sorted descinding by higher score and then sliced to keep only the top 4 results which are then sorted by most recent. 
5. The tag returns the results in the {{ related_entries }} variable as an object array in wich each item is a Statamic entry.

## Seeing some information about the score

If you need to extrapolate more information as how the score was calculated you can use these additional variables that are attached to each related entry. 

```
{{ relbytaxonomy limit="4" taxonomies="tags|categories|products|authors|features" modifiers="1.2|1.5|.5|.5|1" stopwords="with,and,the,use,how,to,as,a,what,you,should,know" }}
    {{ related_entries }}
        <h2>
            <a class=" " href="{{ url }}">{{ title }}</a>
        </h2>
        <div>Score: <b>{{ score | round(2) }}</b></div>
        <div>Word match percentage: <b>{{ word_match_percentage | round(2) }}</b></div>
        <div>Common tags: <b>{{ common_tags | explode(',') | count }}</b></div> 
        <div>{{common_tags}}</div>
        <div>Common taxonomies: <b>{{ found_in_taxonomies | explode(',') | count }}</b></div>
        <div>{{found_in_taxonomies}}</div>
    {{ /related_entries }}
{{ /relbytaxonomy }}
```

- `score`: the total score which is calcuted as 1 point for each taxonomy entry that matches, multiplied by the taxonomy modifier, plus the percentage of words found in the related entry title divided by 10. 
- `word_match_percentage`: the percentage of unique words matched between the titles excluding `stopwords` if they are used. 
- `common_tags`: a comma seperated list of tags that are used in both entries.
- `found_in_taxonomies`: a comma seperated list of the taxonomies that had hits.
