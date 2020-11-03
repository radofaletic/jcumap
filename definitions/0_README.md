## JSON definition files

This folder contains [JSON](https://www.json.org/) (JavaScript) files that provide important information about the structure of degrees and majors presented on this site.

The two files `programs.json` and `majors.json` simply contain lists (as JSON arrays) of the programs and majors that are to be represented on this website.

The other files in this directory describe programs (e.g. `AENGI.json` for *Bachelor of Engineering (Honours)*) and majors (e.g. `RENE-MAJ.json` for *Renewable Energy Systems*).

Looking at the contents of the files will provide a good insight into how the file contents are to be structured. It should be fairly straightforward, however if you need to make any changes and are unsure of working with these types of files please seek assistance from somebody who understands JavaScript, such as a web developer.

The most important aspect of these files is that they contain lists of course codes that correspond to the courses that compose each program core or major. These are listed as an array in the `courses` property.

In the program JSONs you will also notice a property `coursesForAggregating`. This is a subset of the `courses` property that is used by the website when creating aggregate mappings of the constituent courses for each program. The contents of `coursesForAggregating` should be consistent with the degree rules (i.e. *must* contain all of the compulsory courses), and it is recommend to include only the most “basic” courses from any subset from which the degree requires students to only pick one or two.
