## *JCUMap* XML files for individual courses

This folder contains XML files, from the *JCUMap Software Suite*, that describe mappings for ANU courses.

For each course, there are two files, prefixed by the course code as depicted on the P&C website.

1. The first file contains the *JCUMap* mapping data, as inputed to the software (e.g. `ENGN1211.xml` for *ENGN1211*)
2. The second file contains the *JCUMap* mapping result, as is produced by the software (e.g. `ENGN1211MappingResult.xml`)

It is important that in *JCUMap* you enter the actual course code in uppercase.

For courses that are co-badged (e.g. `COMP1110` and `COMP6710`) you may be able to perform a single mapping to cover both course. If this is the case, in *JCUMap* you can enter the course codes with a `/` in between (e.g. `COMP1110/COMP6710`), in which case the file names should use `-` in place of the `/`, for filesystem compatibility requirements (e.g. `COMP1110-COMP6710.xml` and `COMP1110-COMP6710MappingResult.xml`);

You can replace these files with updated versions as required. You may also add additional mappings not already listed here, and the website will automatically include them (as long as the filenames conform to the convention listed above).
