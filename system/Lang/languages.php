<?php
/**
 * Return an array containing all the presents languages in i18n/
 *
 */
function get_lang_list() {

	$lang_list = array(
		'aa' => "Afar",
		'ab' => "Abkhazian",
		'af' => "Afrikaans",
		'am' => "Amharic",
		'an' => "Aragon&#233;s",
		'ar' => "&#1593;&#1585;&#1576;&#1610;",
		'as' => "Assamese",
		'ast' => "Asturianu",
		'ay' => "Aymara",
		'az' => "&#1040;&#1079;&#1241;&#1088;&#1073;&#1072;&#1112;&#1209;&#1072;&#1085;",
		'ba' => "Bashkir",
		'be' => "&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1110;",
		'ber_tam' => "Tamazigh",
		'ber_tam_tfng' => "Tamazigh tifinagh",
		'bg' => "&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;",
		'bh' => "Bihari",
		'bi' => "Bislama",
		'bm' => "Bambara",
		'bn' => "Bengali; Bangla",
		'bo' => "Tibetan",
		'br' => "brezhoneg",
		'bs' => "bosanski",
		'ca' => "Catal&#224;",
		'co' => "Corsu",
		'cpf' => "Kr&eacute;ol r&eacute;yon&eacute;",
		'cpf_dom' => "Krey&ograve;l",
		'cpf_hat' => "Kr&eacute;y&ograve;l (P&eacute;yi Dayiti)",
		'cs' => "&#269;e&#353;tina",
		'cy' => "Cymraeg",	# welsh, gallois
		'da' => "Dansk",
		'de' => "Deutsch",
		'dz' => "Bhutani",
		'el' => "&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;",
		'en' => "English",
		'en_hx' => "H4ck3R",
		'en_sm' => "Smurf",
		'eo' => "Esperanto",
		'es' => "Espa&#241;ol",
		'es_co' => "Colombiano",
		'et' => "Eesti",
		'eu' => "Euskara",
		'fa' => "&#1601;&#1575;&#1585;&#1587;&#1609;",
		'ff' => "Fulah", // peul
		'fi' => "Suomi",
		'fj' => "Fiji",
		'fo' => "F&#248;royskt",
		'fon' => "Fongb&egrave;",
		'fr' => "Fran&#231;ais",
		'fr_sc' => "Schtroumpf",
		'fr_lpc' => "Langue parl&#233;e compl&#233;t&#233;e",
		'fr_lsf' => "Langue des signes fran&#231;aise",
		'fr_spl' => "Fran&#231;ais simplifi&#233;",
		'fr_tu' => "Fran&#231;ais copain",
		'fy' => "Frisian",
		'ga' => "Irish",
		'gd' => "Scots Gaelic",
		'gl' => "Galego",
		'gn' => "Guarani",
		'grc' => "&#7944;&#961;&#967;&#945;&#943;&#945; &#7961;&#955;&#955;&#951;&#957;&#953;&#954;&#942;", // grec ancien
		'gu' => "Gujarati",
		'ha' => "Hausa",
		'hbo' => "&#1506;&#1489;&#1512;&#1497;&#1514;&#1470;&#1492;&#1514;&#1504;&#1498;", // hebreu classique ou biblique
		'he' => "&#1506;&#1489;&#1512;&#1497;&#1514;",
		'hi' => "&#2361;&#2367;&#2306;&#2342;&#2368;",
		'hr' => "Hrvatski",
		'hu' => "Magyar",
		'hy' => "Armenian",
		'ia' => "Interlingua",
		'id' => "Indonesia",
		'ie' => "Interlingue",
		'ik' => "Inupiak",
		'is' => "&#237;slenska",
		'it' => "Italiano",
		'it_fem' => "Italiana",
		'iu' => "Inuktitut",
		'ja' => "&#26085;&#26412;&#35486;",
		'jv' => "Javanese",
		'ka' => "&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312;",
		'kk' => "&#2325;&#2379;&#2306;&#2325;&#2339;&#2368;",
		'kl' => "Kalaallisut",
		'km' => "Cambodian",
		'kn' => "Kannada",
		'ko' => "&#54620;&#44397;&#50612;",
		'ks' => "Kashmiri",
		'ku' => "Kurdish",
		'ky' => "Kirghiz",
		'la' => "lingua latina",
		'lb' => "L&euml;tzebuergesch",
		'ln' => "Lingala",
		'lo' => "&#3742;&#3762;&#3754;&#3762;&#3749;&#3762;&#3751;", # lao
		'lt' => "Lietuvi&#371;",
		'lu' => "Luba-katanga",
		'lv' => "Latvie&#353;u",
		'man' => "Mandingue", # a traduire en mandingue
		'mfv' => "Manjak", # ISO-639-3
		'mg' => "Malagasy",
		'mi' => "Maori",
		'mk' => "&#1084;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080; &#1112;&#1072;&#1079;&#1080;&#1082;",
		'ml' => "Malayalam",
		'mn' => "Mongolian",
		'mo' => "Moldavian",
		'mos' => "Mor&eacute;",
		'mr' => "&#2350;&#2352;&#2366;&#2336;&#2368;",
		'ms' => "Bahasa Malaysia",
		'mt' => "Maltese",
		'my' => "Burmese",
		'na' => "Nauru",
		'nap' => "Napulitano",
		'ne' => "Nepali",
		'nqo' => "N'ko", // www.manden.org
		'nl' => "Nederlands",
		'no' => "Norsk",
		'nb' => "Norsk bokm&aring;l",
		'nn' => "Norsk nynorsk",
		'oc' => "&Ograve;c",
		'oc_lnc' => "&Ograve;c lengadocian",
		'oc_ni' => "&Ograve;c ni&ccedil;ard",
		'oc_ni_la' => "&Ograve;c ni&ccedil;ard (larg)",
		'oc_prv' => "&Ograve;c proven&ccedil;au",
		'oc_gsc' => "&Ograve;c gascon",
		'oc_lms' => "&Ograve;c lemosin",
		'oc_auv' => "&Ograve;c auvernhat",
		'oc_va' => "&Ograve;c vivaroaupenc",
		'om' => "(Afan) Oromo",
		'or' => "Oriya",
		'pa' => "Punjabi",
		'pbb' => 'Nasa Yuwe',
		'pl' => "Polski",
		'ps' => "Pashto, Pushto",
		'pt' => "Portugu&#234;s",
		'pt_br' => "Portugu&#234;s do Brasil",
		'qu' => "Quechua",
		'rm' => "Rhaeto-Romance",
		'rn' => "Kirundi",
		'ro' => "Rom&#226;n&#259;",
		'roa' => "Ch'ti",
		'ru' => "&#1088;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;",
		'rw' => "Kinyarwanda",
		'sa' => "&#2360;&#2306;&#2360;&#2381;&#2325;&#2371;&#2340;",
		'sc' => "Sardu",
		'scn' => "Sicilianu",
		'sd' => "Sindhi",
		'sg' => "Sangho",
		'sh' => "Srpskohrvastski",
		'sh_latn' => 'Srpskohrvastski',
		'sh_cyrl' => '&#1057;&#1088;&#1087;&#1089;&#1082;&#1086;&#1093;&#1088;&#1074;&#1072;&#1090;&#1089;&#1082;&#1080;',
		'si' => "Sinhalese",
		'sk' => "Sloven&#269;ina",	// (Slovakia)
		'sl' => "Sloven&#353;&#269;ina",	// (Slovenia)
		'sm' => "Samoan",
		'sn' => "Shona",
		'so' => "Somali",
		'sq' => "Shqip",
		'sr' => "&#1089;&#1088;&#1087;&#1089;&#1082;&#1080;",
		'src' => 'Sardu logudor&#233;su', // sarde cf 'sc'
		'sro' => 'Sardu campidan&#233;su',
		'ss' => "Siswati",
		'st' => "Sesotho",
		'su' => "Sundanese",
		'sv' => "Svenska",
		'sw' => "Kiswahili",
		'ta' => "&#2980;&#2990;&#3007;&#2996;&#3021;", // Tamil
		'te' => "Telugu",
		'tg' => "Tajik",
		'th' => "&#3652;&#3607;&#3618;",
		'ti' => "Tigrinya",
		'tk' => "Turkmen",
		'tl' => "Tagalog",
		'tn' => "Setswana",
		'to' => "Tonga",
		'tr' => "T&#252;rk&#231;e",
		'ts' => "Tsonga",
		'tt' => "&#1058;&#1072;&#1090;&#1072;&#1088;",
		'tw' => "Twi",
		'ty' => "Reo m&#257;`ohi", // tahitien
		'ug' => "Uighur",
		'uk' => "&#1091;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;",
		'ur' => "&#1649;&#1585;&#1583;&#1608;",
		'uz' => "U'zbek",
		'vi' => "Ti&#7871;ng Vi&#7879;t",
		'vo' => "Volapuk",
		'wa' => "Walon",
		'wo' => "Wolof",
		'xh' => "Xhosa",
		'yi' => "Yiddish",
		'yo' => "Yoruba",
		'za' => "Zhuang",
		'zh' => "&#20013;&#25991;", // chinois (ecriture simplifiee)
		'zh_tw' => "&#21488;&#28771;&#20013;&#25991;", // chinois taiwan (ecr. traditionnelle)
		'zu' => "Zulu"

	);

  return $lang_list;
}

?>
