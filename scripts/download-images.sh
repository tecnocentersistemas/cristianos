#!/bin/bash
# Download free images from Pexels for FaithTunes
# All images are free to use (Pexels License)
set -e

BASE="/var/www/cristianos/media/images"
mkdir -p "$BASE"/{mountains,rivers,sunsets,forests,animals,fields}

echo "=== Downloading landscape images from Pexels ==="

# MOUNTAINS
echo "[mountains]"
wget -q -O "$BASE/mountains/snowy_peak_01.jpg" "https://images.pexels.com/photos/417173/pexels-photo-417173.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  snowy_peak_01 OK"
wget -q -O "$BASE/mountains/mountain_sunrise_02.jpg" "https://images.pexels.com/photos/2098427/pexels-photo-2098427.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  mountain_sunrise_02 OK"
wget -q -O "$BASE/mountains/ridge_clouds_03.jpg" "https://images.pexels.com/photos/1054218/pexels-photo-1054218.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  ridge_clouds_03 OK"
wget -q -O "$BASE/mountains/mountain_lake_04.jpg" "https://images.pexels.com/photos/147411/italy-mountains-dawn-daybreak-147411.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  mountain_lake_04 OK"
wget -q -O "$BASE/mountains/summit_golden_05.jpg" "https://images.pexels.com/photos/2113566/pexels-photo-2113566.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  summit_golden_05 OK"
wget -q -O "$BASE/mountains/rock_formation_01.jpg" "https://images.pexels.com/photos/2559941/pexels-photo-2559941.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  rock_formation_01 OK"

# RIVERS
echo "[rivers]"
wget -q -O "$BASE/rivers/crystal_river_01.jpg" "https://images.pexels.com/photos/2406389/pexels-photo-2406389.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  crystal_river_01 OK"
wget -q -O "$BASE/rivers/waterfall_serene_02.jpg" "https://images.pexels.com/photos/2743287/pexels-photo-2743287.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  waterfall_serene_02 OK"
wget -q -O "$BASE/rivers/lake_calm_03.jpg" "https://images.pexels.com/photos/346529/pexels-photo-346529.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  lake_calm_03 OK"
wget -q -O "$BASE/rivers/waterfall_majestic_01.jpg" "https://images.pexels.com/photos/2438/nature-forest-waves-trees.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  waterfall_majestic_01 OK"
wget -q -O "$BASE/rivers/ocean_waves_01.jpg" "https://images.pexels.com/photos/1032650/pexels-photo-1032650.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  ocean_waves_01 OK"

# SUNSETS
echo "[sunsets]"
wget -q -O "$BASE/sunsets/sunrise_spectacular_01.jpg" "https://images.pexels.com/photos/36717/amazing-animal-beautiful-beauty.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  sunrise_spectacular_01 OK"
wget -q -O "$BASE/sunsets/sunset_golden_01.jpg" "https://images.pexels.com/photos/36744/sunset-cloud-meditation-yoga.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  sunset_golden_01 OK"
wget -q -O "$BASE/sunsets/sunset_purple_02.jpg" "https://images.pexels.com/photos/209831/pexels-photo-209831.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  sunset_purple_02 OK"
wget -q -O "$BASE/sunsets/sky_infinite_01.jpg" "https://images.pexels.com/photos/53594/blue-clouds-day-fluffy-53594.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  sky_infinite_01 OK"
wget -q -O "$BASE/sunsets/starry_sky_01.jpg" "https://images.pexels.com/photos/1252890/pexels-photo-1252890.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  starry_sky_01 OK"
wget -q -O "$BASE/sunsets/sunbeams_03.jpg" "https://images.pexels.com/photos/2559484/pexels-photo-2559484.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  sunbeams_03 OK"

# FORESTS
echo "[forests]"
wget -q -O "$BASE/forests/lush_forest_01.jpg" "https://images.pexels.com/photos/15286/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  lush_forest_01 OK"
wget -q -O "$BASE/forests/light_rays_02.jpg" "https://images.pexels.com/photos/167698/pexels-photo-167698.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  light_rays_02 OK"
wget -q -O "$BASE/forests/forest_path_03.jpg" "https://images.pexels.com/photos/1578750/pexels-photo-1578750.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  forest_path_03 OK"
wget -q -O "$BASE/forests/misty_forest_05.jpg" "https://images.pexels.com/photos/1423600/pexels-photo-1423600.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  misty_forest_05 OK"

# ANIMALS
echo "[animals]"
wget -q -O "$BASE/animals/eagle_soaring_01.jpg" "https://images.pexels.com/photos/2662434/pexels-photo-2662434.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  eagle_soaring_01 OK"
wget -q -O "$BASE/animals/dove_flight_01.jpg" "https://images.pexels.com/photos/1661179/pexels-photo-1661179.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  dove_flight_01 OK"
wget -q -O "$BASE/animals/lamb_meadow_01.jpg" "https://images.pexels.com/photos/288621/pexels-photo-288621.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  lamb_meadow_01 OK"
wget -q -O "$BASE/animals/lion_majestic_01.jpg" "https://images.pexels.com/photos/247502/pexels-photo-247502.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  lion_majestic_01 OK"
wget -q -O "$BASE/animals/deer_meadow_01.jpg" "https://images.pexels.com/photos/1054655/pexels-photo-1054655.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  deer_meadow_01 OK"
wget -q -O "$BASE/animals/butterfly_monarch_01.jpg" "https://images.pexels.com/photos/326055/pexels-photo-326055.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  butterfly_monarch_01 OK"

# FIELDS
echo "[fields]"
wget -q -O "$BASE/fields/green_valley_01.jpg" "https://images.pexels.com/photos/1166209/pexels-photo-1166209.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  green_valley_01 OK"
wget -q -O "$BASE/fields/wildflowers_02.jpg" "https://images.pexels.com/photos/462118/pexels-photo-462118.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  wildflowers_02 OK"
wget -q -O "$BASE/fields/wheat_golden_01.jpg" "https://images.pexels.com/photos/265216/pexels-photo-265216.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  wheat_golden_01 OK"
wget -q -O "$BASE/fields/rolling_hills_04.jpg" "https://images.pexels.com/photos/1227513/pexels-photo-1227513.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&fit=crop" && echo "  rolling_hills_04 OK"

echo ""
echo "=== Download complete ==="
echo "Total images:"
find "$BASE" -name "*.jpg" | wc -l
