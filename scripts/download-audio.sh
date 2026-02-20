#!/bin/bash
# Download free instrumental audio for FaithTunes
# Sources: Free Music Archive (CC0), Pixabay Music (free license)
set -e

BASE="/var/www/cristianos/media/audio"
mkdir -p "$BASE"/{worship,peaceful,upbeat,cinematic}

echo "=== Downloading free instrumental audio ==="

# Peaceful / Worship instrumentals from Pixabay (free for commercial use)
echo "[peaceful]"
wget -q -O "$BASE/peaceful/ambient_peace_01.mp3" "https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3" && echo "  ambient_peace_01 OK"
wget -q -O "$BASE/peaceful/meditation_calm_02.mp3" "https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3" && echo "  meditation_calm_02 OK"
wget -q -O "$BASE/peaceful/gentle_piano_03.mp3" "https://cdn.pixabay.com/audio/2022/08/03/audio_54ca0ffa52.mp3" && echo "  gentle_piano_03 OK"
wget -q -O "$BASE/peaceful/soft_ambient_04.mp3" "https://cdn.pixabay.com/audio/2021/11/25/audio_91b32e02f9.mp3" && echo "  soft_ambient_04 OK"

echo "[worship]"
wget -q -O "$BASE/worship/spiritual_worship_01.mp3" "https://cdn.pixabay.com/audio/2023/10/18/audio_2a55e9726a.mp3" && echo "  spiritual_worship_01 OK"
wget -q -O "$BASE/worship/piano_worship_02.mp3" "https://cdn.pixabay.com/audio/2022/10/25/audio_fae4b85c46.mp3" && echo "  piano_worship_02 OK"
wget -q -O "$BASE/worship/acoustic_worship_03.mp3" "https://cdn.pixabay.com/audio/2023/06/07/audio_b588cae2e1.mp3" && echo "  acoustic_worship_03 OK"

echo "[cinematic]"
wget -q -O "$BASE/cinematic/epic_inspiration_01.mp3" "https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3" && echo "  epic_inspiration_01 OK"
wget -q -O "$BASE/cinematic/cinematic_hopeful_02.mp3" "https://cdn.pixabay.com/audio/2023/04/11/audio_79e6a47a1a.mp3" && echo "  cinematic_hopeful_02 OK"
wget -q -O "$BASE/cinematic/orchestral_uplifting_03.mp3" "https://cdn.pixabay.com/audio/2022/11/22/audio_febc508520.mp3" && echo "  orchestral_uplifting_03 OK"

echo "[upbeat]"
wget -q -O "$BASE/upbeat/happy_acoustic_01.mp3" "https://cdn.pixabay.com/audio/2022/05/16/audio_eca419c4a3.mp3" && echo "  happy_acoustic_01 OK"
wget -q -O "$BASE/upbeat/joyful_guitar_02.mp3" "https://cdn.pixabay.com/audio/2022/10/09/audio_4d1cf20d84.mp3" && echo "  joyful_guitar_02 OK"

echo ""
echo "=== Audio download complete ==="
echo "Total audio files:"
find "$BASE" -name "*.mp3" | wc -l
