#!/bin/bash
# Download free instrumental audio organized by genre for FaithTunes
# Source: Pixabay Music (free license, no attribution required)
set -e

BASE="/var/www/cristianos/media/audio"
mkdir -p "$BASE"/{country,rock,gospel,folk,worship,ballad}

echo "=== Downloading instrumental audio by genre ==="

# COUNTRY - Acoustic, guitar, peaceful
echo "[country]"
wget -q -O "$BASE/country/country_gentle_faith_01.mp3" "https://cdn.pixabay.com/audio/2022/10/09/audio_4d1cf20d84.mp3" && echo "  country_gentle_faith_01 OK" || echo "  FAIL"
wget -q -O "$BASE/country/country_warm_love_01.mp3" "https://cdn.pixabay.com/audio/2023/09/27/audio_3eca0e7e14.mp3" && echo "  country_warm_love_01 OK" || echo "  FAIL"
wget -q -O "$BASE/country/country_gentle_hope_01.mp3" "https://cdn.pixabay.com/audio/2024/11/14/audio_328abc4cde.mp3" && echo "  country_gentle_hope_01 OK" || echo "  FAIL"
wget -q -O "$BASE/country/country_festive_gratitude_01.mp3" "https://cdn.pixabay.com/audio/2022/05/16/audio_eca419c4a3.mp3" && echo "  country_festive_gratitude_01 OK" || echo "  FAIL"
wget -q -O "$BASE/country/country_hopeful_desert_01.mp3" "https://cdn.pixabay.com/audio/2023/04/11/audio_79e6a47a1a.mp3" && echo "  country_hopeful_desert_01 OK" || echo "  FAIL"

# ROCK - Electric guitar, powerful, energetic
echo "[rock]"
wget -q -O "$BASE/rock/rock_powerful_hope_01.mp3" "https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3" && echo "  rock_powerful_hope_01 OK" || echo "  FAIL"
wget -q -O "$BASE/rock/rock_energetic_faith_01.mp3" "https://cdn.pixabay.com/audio/2022/11/22/audio_febc508520.mp3" && echo "  rock_energetic_faith_01 OK" || echo "  FAIL"
wget -q -O "$BASE/rock/rock_mighty_strength_01.mp3" "https://cdn.pixabay.com/audio/2023/07/30/audio_e5765d72e9.mp3" && echo "  rock_mighty_strength_01 OK" || echo "  FAIL"
wget -q -O "$BASE/rock/rock_triumphant_strength_01.mp3" "https://cdn.pixabay.com/audio/2024/09/17/audio_0f85170a21.mp3" && echo "  rock_triumphant_strength_01 OK" || echo "  FAIL"
wget -q -O "$BASE/rock/rock_energetic_gratitude_01.mp3" "https://cdn.pixabay.com/audio/2024/04/15/audio_56b3957dcd.mp3" && echo "  rock_energetic_gratitude_01 OK" || echo "  FAIL"

# GOSPEL - Piano, organ, choir, spiritual
echo "[gospel]"
wget -q -O "$BASE/gospel/gospel_powerful_faith_01.mp3" "https://cdn.pixabay.com/audio/2023/10/18/audio_2a55e9726a.mp3" && echo "  gospel_powerful_faith_01 OK" || echo "  FAIL"
wget -q -O "$BASE/gospel/gospel_soft_peace_01.mp3" "https://cdn.pixabay.com/audio/2022/10/25/audio_fae4b85c46.mp3" && echo "  gospel_soft_peace_01 OK" || echo "  FAIL"
wget -q -O "$BASE/gospel/gospel_epic_strength_01.mp3" "https://cdn.pixabay.com/audio/2023/06/07/audio_b588cae2e1.mp3" && echo "  gospel_epic_strength_01 OK" || echo "  FAIL"
wget -q -O "$BASE/gospel/gospel_jubilant_hope_01.mp3" "https://cdn.pixabay.com/audio/2022/08/31/audio_419263a951.mp3" && echo "  gospel_jubilant_hope_01 OK" || echo "  FAIL"
wget -q -O "$BASE/gospel/gospel_pastoral_love_01.mp3" "https://cdn.pixabay.com/audio/2024/01/10/audio_c97e250c1f.mp3" && echo "  gospel_pastoral_love_01 OK" || echo "  FAIL"

# FOLK - Acoustic, fingerpicking, flute, serene
echo "[folk]"
wget -q -O "$BASE/folk/folk_acoustic_hope_01.mp3" "https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3" && echo "  folk_acoustic_hope_01 OK" || echo "  FAIL"
wget -q -O "$BASE/folk/folk_serene_hope_01.mp3" "https://cdn.pixabay.com/audio/2024/02/07/audio_fd47e4ed5d.mp3" && echo "  folk_serene_hope_01 OK" || echo "  FAIL"
wget -q -O "$BASE/folk/folk_contemplative_peace_01.mp3" "https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3" && echo "  folk_contemplative_peace_01 OK" || echo "  FAIL"
wget -q -O "$BASE/folk/folk_cheerful_gratitude_01.mp3" "https://cdn.pixabay.com/audio/2024/11/04/audio_2d78cf5bde.mp3" && echo "  folk_cheerful_gratitude_01 OK" || echo "  FAIL"
wget -q -O "$BASE/folk/folk_inspiring_hope_01.mp3" "https://cdn.pixabay.com/audio/2023/09/04/audio_e87db2c6b0.mp3" && echo "  folk_inspiring_hope_01 OK" || echo "  FAIL"

# WORSHIP - Piano, pad, strings, peaceful, contemporary
echo "[worship]"
wget -q -O "$BASE/worship/worship_peaceful_01.mp3" "https://cdn.pixabay.com/audio/2021/11/25/audio_91b32e02f9.mp3" && echo "  worship_peaceful_01 OK" || echo "  FAIL"
wget -q -O "$BASE/worship/worship_vibrant_gratitude_01.mp3" "https://cdn.pixabay.com/audio/2022/08/03/audio_54ca0ffa52.mp3" && echo "  worship_vibrant_gratitude_01 OK" || echo "  FAIL"
wget -q -O "$BASE/worship/worship_flowing_love_01.mp3" "https://cdn.pixabay.com/audio/2023/03/13/audio_3b8e0b8ff8.mp3" && echo "  worship_flowing_love_01 OK" || echo "  FAIL"
wget -q -O "$BASE/worship/worship_majestic_love_01.mp3" "https://cdn.pixabay.com/audio/2024/08/26/audio_cee36f5637.mp3" && echo "  worship_majestic_love_01 OK" || echo "  FAIL"
wget -q -O "$BASE/worship/worship_celestial_faith_01.mp3" "https://cdn.pixabay.com/audio/2024/05/20/audio_81e31c37c5.mp3" && echo "  worship_celestial_faith_01 OK" || echo "  FAIL"

# BALLAD - Piano, strings, emotional
echo "[ballad]"
wget -q -O "$BASE/ballad/ballad_piano_love_01.mp3" "https://cdn.pixabay.com/audio/2022/08/03/audio_54ca0ffa52.mp3" && echo "  ballad_piano_love_01 OK" || echo "  FAIL"
wget -q -O "$BASE/ballad/ballad_emotional_gratitude_01.mp3" "https://cdn.pixabay.com/audio/2023/05/16/audio_146d3e0805.mp3" && echo "  ballad_emotional_gratitude_01 OK" || echo "  FAIL"
wget -q -O "$BASE/ballad/ballad_night_faith_01.mp3" "https://cdn.pixabay.com/audio/2024/03/11/audio_3be8bfa37c.mp3" && echo "  ballad_night_faith_01 OK" || echo "  FAIL"
wget -q -O "$BASE/ballad/ballad_tender_peace_01.mp3" "https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3" && echo "  ballad_tender_peace_01 OK" || echo "  FAIL"
wget -q -O "$BASE/ballad/ballad_emotional_faith_01.mp3" "https://cdn.pixabay.com/audio/2024/06/18/audio_4cb1cc0bde.mp3" && echo "  ballad_emotional_faith_01 OK" || echo "  FAIL"

echo ""
echo "=== Audio download complete ==="
echo "Total audio files by genre:"
for g in country rock gospel folk worship ballad; do
    count=$(find "$BASE/$g" -name "*.mp3" -not -name ".gitkeep" | wc -l)
    echo "  $g: $count files"
done
echo "Total:"
find "$BASE" -name "*.mp3" -not -name ".gitkeep" | wc -l
