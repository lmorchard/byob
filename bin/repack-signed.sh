#!/bin/sh
set -e
IFS=$'\n'
rm -rf unpacked app.7z
for f in `find . -name *.exe`; do
    rm -rf unpacked app.7z
    mkdir unpacked
    if [ -f "$f.repack" ]; then
	echo "Skipping $f"
	continue
    fi
    cd unpacked
    echo Processing $f
    7za x "../$f"
    7za a -r -t7z -mx -m0=BCJ2 -m1=LZMA:d24 -m2=LZMA:d19 -m3=LZMA:d19 -mb0:1 -mb0s1:2 -mb0s2:3 ../app.7z *
    cd ..
    cat 7zSD.sfx.compressed app.tag app.7z > "$f.repack"
    rm -f "$f"
    mv "$f.repack" "$f"
done
rm -rf unpacked app.7z
