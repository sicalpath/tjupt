#!/bin/sh

for i in attachments banners bitbucket config subs tmp torrents upload
do
ln -sv "/data/pt.tju.edu.cn/$i"
done
