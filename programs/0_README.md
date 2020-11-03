## *JCUMap* XML files for degree programs

This folder contains XML files, from the *JCUMap Software Suite*, that describe mappings for degree programs.

For each degree program, there are two files, prefixed by the program code as depicted on the P&C website.

1. The first file contains the *JCUMap* mapping data, as inputed to the software (e.g. `BARTS.xml` for *Bachelor of Arts*)
2. The second file contains the *JCUMap* mapping result, as is produced by the software (e.g. `BARTSMappingResult.xml`)

It should be noted that the *JCUMap* software has numerous limitations that need to be manually rectified. The most important of these is that it only allows the user to indicate up to *24* units for a course. However, since we are concerned with *programs* here, and not *courses*, which typically have many more units that this, we need to take care.

For the first time in create the mapping files, you will need to select an arbitrary unit value. Once you have completed the mapping and saved the file, you can simply double-click on the mapping XML data file which will open in a plain text editor. Manually change the value of `<CreditPoints>` to the appropriate value, e.g. for an engineering degree, you will probably want to change this to `144` or `156`, which represent the full degree (`192`) minus ANU electives (`48` or `36`). However, for a non-engineering degree you will want to change this to `96` (i.e. two years of study as part of an FDD combination). Then save the file, and reload it in *JCUMap* and proceed to generating the MappingResult file. Note that *JCUMap* will retain this new value, even though it will not display it in the software.

The second issue to be aware of is that doing this type of mapping requires you to enter a single “dummy” assessment item. It should have a blank name, and you may distribute this item amongst the learning outcomes any way you think is reasonable (evenly distributed would make most sense, in most instances).
