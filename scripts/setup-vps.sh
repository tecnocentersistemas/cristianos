#!/bin/bash
# ============================================
# FaithTunes - VPS Master Setup Script
# Downloads images, audio, installs fonts,
# and generates demo videos with FFmpeg
# ============================================
set -e

PROJECT="/var/www/cristianos"
IMAGES="$PROJECT/media/images"
AUDIO="$PROJECT/media/audio"
VIDEOS="$PROJECT/media/videos"

echo "========================================"
echo "  FaithTunes VPS Setup"
echo "========================================"

# --- 1. Create directories ---
echo ""
echo "[1/5] Creating directories..."
mkdir -p "$IMAGES"/{mountains,rivers,sunsets,forests,animals,fields}
mkdir -p "$AUDIO"/{peaceful,worship,cinematic,upbeat}
mkdir -p "$VIDEOS"
echo "  Directories OK"

# --- 2. Install fonts for text overlay ---
echo ""
echo "[2/5] Installing fonts..."
apt-get install -y -qq fonts-dejavu-core fonts-liberation > /dev/null 2>&1 || true
fc-cache -f > /dev/null 2>&1 || true
echo "  Fonts OK"

# --- 3. Download images from Pexels (free license) ---
echo ""
echo "[3/5] Downloading images from Pexels..."

download_img() {
  local url="$1"
  local dest="$2"
  if [ -f "$dest" ] && [ -s "$dest" ]; then
    echo "  SKIP $(basename $dest) (exists)"
    return 0
  fi
  wget -q --timeout=15 -O "$dest" "$url" 2>/dev/null
  if [ -s "$dest" ]; then
    echo "  OK   $(basename $dest)"
  else
    echo "  FAIL $(basename $dest)"
    rm -f "$dest"
  fi
}

# Mountains
download_img "https://images.pexels.com/photos/417173/pexels-photo-417173.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/mountains/snowy_peak_01.jpg"
download_img "https://images.pexels.com/photos/2098427/pexels-photo-2098427.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/mountains/mountain_sunrise_02.jpg"
download_img "https://images.pexels.com/photos/1054218/pexels-photo-1054218.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/mountains/ridge_clouds_03.jpg"
download_img "https://images.pexels.com/photos/147411/italy-mountains-dawn-daybreak-147411.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/mountains/mountain_lake_04.jpg"
download_img "https://images.pexels.com/photos/2113566/pexels-photo-2113566.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/mountains/summit_golden_05.jpg"

# Rivers / Water
download_img "https://images.pexels.com/photos/2406389/pexels-photo-2406389.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/rivers/crystal_river_01.jpg"
download_img "https://images.pexels.com/photos/2743287/pexels-photo-2743287.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/rivers/waterfall_serene_02.jpg"
download_img "https://images.pexels.com/photos/346529/pexels-photo-346529.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/rivers/lake_calm_03.jpg"
download_img "https://images.pexels.com/photos/1032650/pexels-photo-1032650.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/rivers/ocean_waves_01.jpg"

# Sunsets / Skies
download_img "https://images.pexels.com/photos/36717/amazing-animal-beautiful-beauty.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/sunsets/sunrise_spectacular_01.jpg"
download_img "https://images.pexels.com/photos/209831/pexels-photo-209831.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/sunsets/sunset_purple_02.jpg"
download_img "https://images.pexels.com/photos/53594/blue-clouds-day-fluffy-53594.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/sunsets/sky_infinite_01.jpg"
download_img "https://images.pexels.com/photos/1252890/pexels-photo-1252890.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/sunsets/starry_sky_01.jpg"
download_img "https://images.pexels.com/photos/2559484/pexels-photo-2559484.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/sunsets/sunbeams_03.jpg"

# Forests
download_img "https://images.pexels.com/photos/15286/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/forests/lush_forest_01.jpg"
download_img "https://images.pexels.com/photos/167698/pexels-photo-167698.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/forests/light_rays_02.jpg"
download_img "https://images.pexels.com/photos/1578750/pexels-photo-1578750.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/forests/forest_path_03.jpg"
download_img "https://images.pexels.com/photos/1423600/pexels-photo-1423600.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/forests/misty_forest_05.jpg"

# Animals
download_img "https://images.pexels.com/photos/2662434/pexels-photo-2662434.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/eagle_soaring_01.jpg"
download_img "https://images.pexels.com/photos/1661179/pexels-photo-1661179.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/dove_flight_01.jpg"
download_img "https://images.pexels.com/photos/288621/pexels-photo-288621.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/lamb_meadow_01.jpg"
download_img "https://images.pexels.com/photos/247502/pexels-photo-247502.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/lion_majestic_01.jpg"
download_img "https://images.pexels.com/photos/1054655/pexels-photo-1054655.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/deer_meadow_01.jpg"
download_img "https://images.pexels.com/photos/326055/pexels-photo-326055.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/animals/butterfly_monarch_01.jpg"

# Fields
download_img "https://images.pexels.com/photos/1166209/pexels-photo-1166209.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/fields/green_valley_01.jpg"
download_img "https://images.pexels.com/photos/462118/pexels-photo-462118.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/fields/wildflowers_02.jpg"
download_img "https://images.pexels.com/photos/265216/pexels-photo-265216.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/fields/wheat_golden_01.jpg"
download_img "https://images.pexels.com/photos/1227513/pexels-photo-1227513.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" "$IMAGES/fields/rolling_hills_04.jpg"

echo "  Images total: $(find $IMAGES -name '*.jpg' | wc -l)"

# --- 4. Download audio from Pixabay (free license) ---
echo ""
echo "[4/5] Downloading audio from Pixabay..."

download_audio() {
  local url="$1"
  local dest="$2"
  if [ -f "$dest" ] && [ -s "$dest" ]; then
    echo "  SKIP $(basename $dest) (exists)"
    return 0
  fi
  wget -q --timeout=30 -O "$dest" "$url" 2>/dev/null
  if [ -s "$dest" ]; then
    echo "  OK   $(basename $dest)"
  else
    echo "  FAIL $(basename $dest)"
    rm -f "$dest"
  fi
}

download_audio "https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3" "$AUDIO/peaceful/ambient_peace_01.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/02/22/audio_d1718ab41b.mp3" "$AUDIO/peaceful/meditation_calm_02.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/08/03/audio_54ca0ffa52.mp3" "$AUDIO/peaceful/gentle_piano_03.mp3"
download_audio "https://cdn.pixabay.com/audio/2021/11/25/audio_91b32e02f9.mp3" "$AUDIO/peaceful/soft_ambient_04.mp3"
download_audio "https://cdn.pixabay.com/audio/2023/10/18/audio_2a55e9726a.mp3" "$AUDIO/worship/spiritual_worship_01.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/10/25/audio_fae4b85c46.mp3" "$AUDIO/worship/piano_worship_02.mp3"
download_audio "https://cdn.pixabay.com/audio/2023/06/07/audio_b588cae2e1.mp3" "$AUDIO/worship/acoustic_worship_03.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/01/18/audio_d0a13f69d2.mp3" "$AUDIO/cinematic/epic_inspiration_01.mp3"
download_audio "https://cdn.pixabay.com/audio/2023/04/11/audio_79e6a47a1a.mp3" "$AUDIO/cinematic/cinematic_hopeful_02.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/11/22/audio_febc508520.mp3" "$AUDIO/cinematic/orchestral_uplifting_03.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/05/16/audio_eca419c4a3.mp3" "$AUDIO/upbeat/happy_acoustic_01.mp3"
download_audio "https://cdn.pixabay.com/audio/2022/10/09/audio_4d1cf20d84.mp3" "$AUDIO/upbeat/joyful_guitar_02.mp3"

echo "  Audio total: $(find $AUDIO -name '*.mp3' | wc -l)"

# --- 5. Generate demo videos with FFmpeg ---
echo ""
echo "[5/5] Generating demo videos with FFmpeg..."

FONT="/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
if [ ! -f "$FONT" ]; then
  FONT="/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"
fi

generate_video() {
  local VIDEO_NAME="$1"
  local IMG1="$2"
  local IMG2="$3"
  local IMG3="$4"
  local IMG4="$5"
  local IMG5="$6"
  local AUDIO_FILE="$7"
  local VERSE1_TEXT="$8"
  local VERSE1_REF="$9"
  local VERSE2_TEXT="${10}"
  local VERSE2_REF="${11}"
  local VERSE3_TEXT="${12}"
  local VERSE3_REF="${13}"
  local OUTPUT="$VIDEOS/${VIDEO_NAME}.mp4"
  local THUMB="$VIDEOS/${VIDEO_NAME}_thumb.jpg"

  if [ -f "$OUTPUT" ] && [ -s "$OUTPUT" ]; then
    echo "  SKIP $VIDEO_NAME (exists)"
    return 0
  fi

  # Verify files exist
  for img in "$IMG1" "$IMG2" "$IMG3" "$IMG4" "$IMG5"; do
    if [ ! -f "$img" ]; then
      echo "  SKIP $VIDEO_NAME (missing image: $(basename $img))"
      return 1
    fi
  done
  if [ ! -f "$AUDIO_FILE" ]; then
    echo "  SKIP $VIDEO_NAME (missing audio)"
    return 1
  fi

  # Get audio duration (cap at 60 seconds for demo)
  local AUDIO_DUR=$(ffprobe -v quiet -show_entries format=duration -of csv=p=0 "$AUDIO_FILE" 2>/dev/null | cut -d'.' -f1)
  if [ -z "$AUDIO_DUR" ] || [ "$AUDIO_DUR" -gt 60 ]; then
    AUDIO_DUR=60
  fi
  local SLIDE_DUR=$(( AUDIO_DUR / 5 ))
  if [ "$SLIDE_DUR" -lt 6 ]; then SLIDE_DUR=6; fi

  # Build FFmpeg command
  # Each image shown for SLIDE_DUR seconds with Ken Burns zoom effect
  # Text overlay with verse on images 2, 3, 4
  ffmpeg -y \
    -loop 1 -t $SLIDE_DUR -i "$IMG1" \
    -loop 1 -t $SLIDE_DUR -i "$IMG2" \
    -loop 1 -t $SLIDE_DUR -i "$IMG3" \
    -loop 1 -t $SLIDE_DUR -i "$IMG4" \
    -loop 1 -t $SLIDE_DUR -i "$IMG5" \
    -i "$AUDIO_FILE" \
    -filter_complex "
      [0:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,zoompan=z='min(zoom+0.0015,1.3)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SLIDE_DUR*25)):s=1920x1080:fps=25,fade=t=out:st=$((SLIDE_DUR-1)):d=1[v0];
      [1:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,zoompan=z='min(zoom+0.0015,1.3)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SLIDE_DUR*25)):s=1920x1080:fps=25,fade=t=in:st=0:d=1,fade=t=out:st=$((SLIDE_DUR-1)):d=1,drawtext=fontfile=$FONT:text='${VERSE1_TEXT}':fontcolor=white:fontsize=38:x=(w-text_w)/2:y=(h-text_h)/2-30:borderw=3:bordercolor=black@0.7,drawtext=fontfile=$FONT:text='— ${VERSE1_REF}':fontcolor=#d4a017:fontsize=30:x=(w-text_w)/2:y=(h-text_h)/2+30:borderw=2:bordercolor=black@0.7[v1];
      [2:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,zoompan=z='min(zoom+0.0015,1.3)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SLIDE_DUR*25)):s=1920x1080:fps=25,fade=t=in:st=0:d=1,fade=t=out:st=$((SLIDE_DUR-1)):d=1,drawtext=fontfile=$FONT:text='${VERSE2_TEXT}':fontcolor=white:fontsize=38:x=(w-text_w)/2:y=(h-text_h)/2-30:borderw=3:bordercolor=black@0.7,drawtext=fontfile=$FONT:text='— ${VERSE2_REF}':fontcolor=#d4a017:fontsize=30:x=(w-text_w)/2:y=(h-text_h)/2+30:borderw=2:bordercolor=black@0.7[v2];
      [3:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,zoompan=z='min(zoom+0.0015,1.3)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SLIDE_DUR*25)):s=1920x1080:fps=25,fade=t=in:st=0:d=1,fade=t=out:st=$((SLIDE_DUR-1)):d=1,drawtext=fontfile=$FONT:text='${VERSE3_TEXT}':fontcolor=white:fontsize=38:x=(w-text_w)/2:y=(h-text_h)/2-30:borderw=3:bordercolor=black@0.7,drawtext=fontfile=$FONT:text='— ${VERSE3_REF}':fontcolor=#d4a017:fontsize=30:x=(w-text_w)/2:y=(h-text_h)/2+30:borderw=2:bordercolor=black@0.7[v3];
      [4:v]scale=1920:1080:force_original_aspect_ratio=increase,crop=1920:1080,zoompan=z='min(zoom+0.0015,1.3)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=$((SLIDE_DUR*25)):s=1920x1080:fps=25,fade=t=in:st=0:d=1[v4];
      [v0][v1][v2][v3][v4]concat=n=5:v=1:a=0[outv]
    " \
    -map "[outv]" -map 5:a \
    -c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p \
    -c:a aac -b:a 128k \
    -t $((SLIDE_DUR * 5)) \
    -shortest \
    -movflags +faststart \
    "$OUTPUT" 2>/dev/null

  if [ -f "$OUTPUT" ] && [ -s "$OUTPUT" ]; then
    # Generate thumbnail from first frame
    ffmpeg -y -i "$OUTPUT" -vframes 1 -q:v 2 "$THUMB" 2>/dev/null
    local SIZE=$(du -h "$OUTPUT" | cut -f1)
    echo "  OK   $VIDEO_NAME ($SIZE)"
  else
    echo "  FAIL $VIDEO_NAME"
  fi
}

# --- Generate 3 demo videos ---

echo "  Generating: montanas_de_fe (country + mountains + faith)..."
generate_video "montanas_de_fe" \
  "$IMAGES/mountains/snowy_peak_01.jpg" \
  "$IMAGES/mountains/mountain_sunrise_02.jpg" \
  "$IMAGES/mountains/ridge_clouds_03.jpg" \
  "$IMAGES/mountains/mountain_lake_04.jpg" \
  "$IMAGES/mountains/summit_golden_05.jpg" \
  "$AUDIO/peaceful/gentle_piano_03.mp3" \
  "Todo lo puedo en Cristo que me fortalece" "Filipenses 4:13" \
  "Fiate de Jehova de todo tu corazon" "Proverbios 3:5" \
  "Por fe andamos, no por vista" "2 Corintios 5:7"

echo "  Generating: rios_de_paz (worship + rivers + peace)..."
generate_video "rios_de_paz" \
  "$IMAGES/rivers/crystal_river_01.jpg" \
  "$IMAGES/rivers/waterfall_serene_02.jpg" \
  "$IMAGES/rivers/lake_calm_03.jpg" \
  "$IMAGES/rivers/ocean_waves_01.jpg" \
  "$IMAGES/forests/misty_forest_05.jpg" \
  "$AUDIO/worship/piano_worship_02.mp3" \
  "Estad quietos, y conoced que yo soy Dios" "Salmo 46:10" \
  "La paz os dejo, mi paz os doy" "Juan 14:27" \
  "No temas, porque yo estoy contigo" "Isaias 41:10"

echo "  Generating: aguilas_del_cielo (cinematic + animals + hope)..."
generate_video "aguilas_del_cielo" \
  "$IMAGES/animals/eagle_soaring_01.jpg" \
  "$IMAGES/sunsets/sunrise_spectacular_01.jpg" \
  "$IMAGES/animals/lion_majestic_01.jpg" \
  "$IMAGES/fields/green_valley_01.jpg" \
  "$IMAGES/animals/deer_meadow_01.jpg" \
  "$AUDIO/cinematic/epic_inspiration_01.mp3" \
  "Los que esperan en Jehova tendran nuevas fuerzas" "Isaias 40:31" \
  "Yo se los planes que tengo para ustedes" "Jeremias 29:11" \
  "Esfuerzate y se valiente" "Josue 1:9"

echo ""
echo "========================================"
echo "  Setup Complete!"
echo "========================================"
echo "  Images: $(find $IMAGES -name '*.jpg' | wc -l)"
echo "  Audio:  $(find $AUDIO -name '*.mp3' | wc -l)"
echo "  Videos: $(find $VIDEOS -name '*.mp4' | wc -l)"
echo ""
echo "  Videos generated:"
ls -lh "$VIDEOS"/*.mp4 2>/dev/null || echo "  (none)"
echo ""
echo "  View at: https://cristianos.centralchat.pro/"
echo "========================================"
