<?php 

// UniFaction Dropdown Menu
WidgetLoader::add("UniFactionMenu", 10, '
<div class="menu-wrap hide-600">
	<ul class="menu">' . (isset($uniMenu) ? $uniMenu : '') . '
		
		<li class="menu-slot show-tablet"><a href="' . URL::unn_today() . '">News</a><ul><li class="dropdown-slot"><a href="' . URL::unn_today() . '">World News</a></li><li class="dropdown-slot"><a href="' . URL::unn_today() . '/USA">US News</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::entertainment_unifaction_com() . '">Entertainment</a><ul><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Books">Books</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Gaming">Gaming</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Movies">Movies</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Music">Music</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Shows">Shows</a></li><li class="dropdown-slot"><a href="' . URL::thenooch_org() . '">The Nooch</a></li></ul>
		
		</li><li class="menu-slot hide-800"><a href="' . URL::sports_unifaction_com() . '">Sports</a><ul><li class="dropdown-slot"><a href="' . URL::gotrek_today() . '">GoTrek</a></li></ul>
		
		</li><li class="menu-slot hide-800"><a href="' . URL::tech_unifaction_com() . '">Tech</a>
		</li><li class="menu-slot hide-1000"><a href="' . URL::science_unifaction_com() . '">Science</a>
		</li><li class="menu-slot hide-1000"><a href="' . URL::design4_today() . '">Design4</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::fashion_unifaction_com() . '">Fashion</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::travel_unifaction_com() . '">Travel</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::food_unifaction_com() . '">Food</a>
		
		</li><li class="menu-slot show-1200"><a href="' . URL::sports_unifaction_com() . '">More</a><ul><li class="dropdown-slot show-800"><a href="' . URL::sports_unifaction_com() . '">Sports</a></li><li class="dropdown-slot show-800"><a href="' . URL::gotrek_today() . '">GoTrek</a></li><li class="dropdown-slot show-800"><a href="' . URL::tech_unifaction_com() . '">Tech</a></li><li class="dropdown-slot show-1000"><a href="' . URL::science_unifaction_com() . '">Science</a></li><li class="dropdown-slot show-1000"><a href="' . URL::design4_today() . '">Design4</a></li><li class="dropdown-slot"><a href="' . URL::fashion_unifaction_com() . '">Fashion</a></li><li class="dropdown-slot"><a href="' . URL::travel_unifaction_com() . '">Travel</a></li><li class="dropdown-slot"><a href="' . URL::food_unifaction_com() . '">Food</a></li></ul>
		
	</ul>
</div>');