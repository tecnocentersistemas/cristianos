#!/bin/bash
cd /var/www/cristianos
# Remove duplicate songs (same title+duration, different unknown_ taskIds)
for ID in e165afe9ce a29d79ac85 778ea0142e; do
  rm -f data/songs/${ID}.json
  rm -f media/audio/songs/${ID}*
  rm -f media/images/slides/${ID}*
  rm -f media/videos/${ID}*
done
# Also clean temp diagnostic files
rm -f api/diag*.php api/diagnostic.php
# Clean chain script if done
echo "Cleaned duplicates: e165afe9ce a29d79ac85 778ea0142e"
ls data/songs/*.json | wc -l
