## CECS professional accreditation website

This website is written in plain PHP (https://www.php.net/), and produce easily readable web pages from the output XML files from the *JCUMap* software.

You will find additional `0_README.md` files in each of the sub-directories, which provide specific detail about the format of each file in those directories. *This* file provides general instructions for managing this website.

In general, this website should not require any maintenance. The main `index.php` script, along with auxiliary functions defined in `jcumap-output-processor.php`, does all of the work in reading the XML files and serving appropriate HTML content for your web server.

* The `definitions/` directory contains JSON files which provide simple definitions for the structure of the engineering degree programs and majors depicted on this website.

* The `programs/` directory contains *JCUMap* XML files that have been generated from program learning outcomes found on the P&C website. It should be noted that *JCUMap* was not designed to produce these mappings, and there are several important caveats and manual edits that you will be required to make, should you wish to add any additional mappings or edit any of the existing ones via *JCUMap*. The `0_README.md` file in that directory contains the specifics.

* The `majors/` directory is currently unused, but in the future it may be possible to use it in a similar way to the `programs/` directory.

* The `courses/` director contains *JCUMap* XML files from individual courses - both engineering and non-engineering courses. There are no special instructions for these files, other a few minor items mentioned in that directory’s `0_README.md` file. If you need to update any of the course mappings, simply upload the new XML files to this directory and overwrite the old files. Similarly, to add a new course to the site, simple upload its XML files, and the website will automatically recognise it.
