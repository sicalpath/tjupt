#!/bin/sh

PT_DATADIR="/pt-data/tjupt-data"

for i in attachments banners bitbucket config subs tmp torrents upload
do
ln -sv "$PT_DATADIR/$i"
done

ln -sv "$PT_DATADIR/imdb/cache" "imdb/"

