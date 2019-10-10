<?php
/**
 * Created by PhpStorm.
 * User: Indrajeet
 * Date: 11/7/19
 * Time: 6:08 PM
 */
require_once('../../app/Mage.php');
umask(0);
Mage::app();
echo "<pre>";

$helper = Mage::helper("allure_salesforce/salesforceClient");
$emailFormatterHelper = Mage::helper('allure_salesforce/emailFormatter');
$customerCollection = Mage::getModel('customer/customer')->getCollection()
->addFieldToFilter('entity_id',array('in' => array(3551,4364,4413,9813,12530,43845,44458,45036,45736,46145,46215,46902,46920,47021,47315,47327,47522,47524,47742,47884,47939,48039,48603,48608,48692,48848,49609,49643,49661,49662,52245,52398,54754,58225,64832,66016,66640,67538,68926,70158,70424,71487,139757,140935,142942,143242,143460,143551,143934,143944,144129,144141,144246,144282,144299,144323,144531,144574,144624,144764,144805,144875,144887,144890,144906,145403,146334,147692,149071,150413,151272,151295,151324,151336,151374,151395,151402,151403,151414,151421,151422,151437,151443,151447,151458,151469,151471,151474,151478,151485,151499,151502,151506,151544,151550,151567,151602,151605,151682,151690,151697,151699,151709,151720,151722,151748,151749,151773,151809,151822,151823,151824,151827,151828,151830,151837,151844,151862,151884,151893,151925,151942,151943,151952,151988,151989,151997,152008,152015,152017,152038,152078,152085,152128,152144,152159,152166,152223,152225,152231,152248,152282,152312,152321,152349,152363,152370,152392,152476,152579,152595,152757,152849,152957,153033,153586,153833,153937,153950,153992,154013,154063,154064,154065,154066,154069,154070,154071,154072,154074,154075,154076,154077,154079,154080,154081,154083,154084,154085,154086,154087,154088,154089,154090,154091,154092,154093,154094,154095,154096,154097,154098,154099,154100,154101,154103,154106,154107,154108,154109,154110,154112,154114,154116,154118,154119,154120,154122,154123,154124,154125,154129,154130,154131,154132,154133,154134,154135,154136,154137,154161,154167,154169,154170,154171,154172,154173,154175,154176,154177,154178,154180,154181,154182,154183,154184,154185,154186,154187,154188,154190,154191,154192,154193,154198,154199,154200,154202,154203,154205,154207,154209,154210,154211,154212,154213,154214,154215,154216,154218,154219,154220,154221,154223,154224,154225,154226,154227,154228,154231,154232,154233,154234,154235,154236,154237,154238,154239,154240,154241,154242,154244,154245,154247,154248,154249,154251,154252,154253,154255,154256,154260,154261,154263,154264,154272,154273,154274,154275,154276,154277,154279,154280,154281,154282,154283,154284,154285,154286,154287,154288,154289,154290,154291,154293,154295,154298,154299,154300,154301,154302,154303,154304,154305,154308,154309,154310,154315,154316,154318,154321,154322,154323,154324,154325,154328,154330,154331,154332,154333,154334,154335,154336,154337,154338,154339,154340,154341,154342,154343,154344,154345,154346,154347,154348,154349,154351,154352,154353,154354,154355,154356,154357,154358,154360,154361,154373,154374,154375,154376,154377,154378,154379,154380,154381,154382,154383,154384,154385,154386,154387,154388,154389,154390,154391,154392,154393,154394,154395,154396,154397,154399,154400,154403,154404,154406,154408,154409,154413,154414,154415,154416,154417,154418,154420,154421,154422,154423,154425,154426,154428,154429,154430,154431,154432,154434,154435,154436,154437,154439,154441,154442,154443,154444,154445,154446,154447,154448,154449,154450,154451,154452,154453,154454,154455,154456,154457,154458,154459,154460,154461,154462,154463,154465,154466,154469,154472,154476,154489,154491,154492,154493,154494,154495,154496,154497,154498,154503,154504,154505,154506,154507,154508,154509,154510,154511,154512,154513,154514,154515,154516,154517,154518,154519,154520,154521,154522,154523,154524,154525,154526,154527,154528,154529,154530,154531,154533,154534,154535,154536,154537,154538,154539,154541,154542,154543,154544,154545,154546,154547,154548,154549,154550,154551,154552,154553,154555,154556,154557,154558,154559,154560,154561,154562,154563,154564,154565,154567,154568,154569,154571,154572,154573,154575,154576,154577,154578,154580,154581,154582,154583,154584,154585,154587,154589,154590,154591,154592,154593,154596,154597,154598,154601,154602,154603,154604,154605,154606,154607,154608,154609,154610,154611,154612,154613,154615,154616,154617,154618,154619,154620,154621,154622,154623,154641,154726,154741,154784,154810,154811,154814,154815,154816,154821,154822,154823,154826,154827,154828,154829,154830,154831,154832,154833,154834,154835,154836,154837,154842,154843,154844,154845,154846,154847,154848,154849,154852,154853,154854,154855,154856,154857,154858,154859,154860,154861,154862,154864,154865,154866,154867,154869,154871,154872,154873,154874,154875,154876,154877,154878,154879,154880,154881,154883,154884,154885,154886,154887,154888,154889,154890,154891,154894,154895,154896,154897,154898,154899,154900,154901,154902,154904,154905,154906,154908,154911,154913,154914,154915,154916,154919,154920,154922,154925,154926,154929,154930,154931,154932,154933,154934,154935,154936,154937,154938,154940,154941,154942,154943,154944,154945,154946,154947,154948,154950,154951,154952,154953,154954,154955,154956,154957,154958,154983,154985,154986,154998,155000,155001,155003,155004,155005,155006,155007,155008,155011,155012,155013,155017,155018,155019,155020,155022,155024,155026,155027,155028,155029,155030,155031,155032,155033,155037,155038,155039,155040,155041,155044,155045,155046,155047,155049,155050,155051,155054,155055,155056,155057,155058,155059,155060,155061,155062,155063,155065,155066,155067,155069,155071,155076,155077,155078,155079,155080,155081,155082,155083,155084,155085,155086,155087,155088,155089,155090,155091,155092,155097,155098,155099,155100,155101,155102,155103,155105,155106,155109,155114,155115,155116,155117,155118,155119,155120,155121,155122,155123,155124,155125,155128,155129,155130,155133,155137,155139,155140,155141,155142,155143,155144,155145,155146,155147,155148,155149,155150,155151,155154,155155,155156,155157,155158,155163,155167,155168,155169,155171,155172,155173,155175,155176,155177,155178,155179,155180,155181,155182,155183,155184,155193,155194,155196,155197,155199,155200,155201,155202,155203,155204,155205,155206,155208,155209,155211,155214,155216,155218,155220,155222,155226,155229,155232,155235,155236,155237,155240,155241,155242,155243,155245,155246,155248,155249,155250,155260,155277,155287,155288,155302,155308,155311,155314,155315,155316,155317,155318,155355,155364,155386,155411,155448,155449,155450,155451,155454,155455,155459,155462,155464,155488,155507,155562,155581,155608,155610,155611,155620,155628,155630,155631,155632,155633,155634,155635,155636,155637,155638,155639,155640,155641,155642,155644,155645,155646,155647,155648,155649,155650,155652,155653,155654,155655,155656,155657,155658,155659,155660,155661,155662,155663,155664,155665,155666,155668,155670,155672,155673,155674,155675,155676,155679,155680,155681,155682,155683,155685,155686,155690,155699,155700,155704,155709,155716,155723,155725,155726,155730,155731,156445,156449,156829,156830,156861,157016,157337,157565,157770,157777,157803,157821,157848,157849,157850,157917,157919,157934,157945,157949,157950,158128,158236,158263,158264,158309,158310,158311,158313,158315,158316,158317,158318,158319,158321,158322,158323,158324,158325,158327,158328,158331,158356,158357,158358,158359,158395,158769,158794,158930,159321,159520,159600,159606,159607,159608,159611,159617,159618,159619,159620,159621,159622,159623,159624,159625,159626,159654,159657,159666,159671,159678,159717,159734,159742,159743,159744,159988,159989,159990,160107,160162,160235,160252,160638,160698,161003,161090,161199,161329,161351,161362,161366,161400,161709,161926,162313,162315,162524,162782,163003,163015,163453,163604,164159,164160,164468,164721,164790,165282,165313,165447,166306,166319,167090,167288,167723,168164,169473,169841,169844,169850,169997,170146,170229,170728,171840,172663,172789,173047,173420,173426,173427,173431,173432,173436,173439,173453,173454,173465,173470,173504,173527,173541,173553,173563,173574,173584,173589,173598,173600,173608,173630,173636,173638,173761,173765,173801,173978,174106,174684,174967,175079,175098,175160,175243,175262,175401,175417,175768,176351,176471,176525,176694,177069,177454,177455,177460,177496,177644,177826,177829,177835,177836,177841,177844,177865,177866,177873,177874,177877,177891,177892,177893,177899,177900,177907,177908,177910,177911,177913,177932,177938,177939,177943,177946,177948,177949,177960,177966,177976,177982,178009,178010,178014,178019,178026,178033,178049,178066,178068,178069,178070,178071,178072,178094,178107,178117,178118,178132,178135,178136,178138,178159,178160,178163,178166,178171,178177,178184,178202,178208,178212,178214,178215,178217,178219,178221,178228,178229,178233,178234,178243,178245,178247,178248,178258,178261,178262,178264,178266,178267,178270,178271,178275,178276,178277,178278,178279,178280,178281,178282,178283,178284,178285,178286,178287,178288,178376,178378,178416,178417,178420,178421,178422,178958,178985,179025,179030,180323,180343,180360,180445,180613,180864,180974,181258,181267,181293,181297,181307,181533,181573,181855,181940,182061,182318,182506,182657,182658,182801,183316,183360,183472,183724,183907,183934,183935,183936,183964,183977,183982,184010,184011,184041,184067,184146,184165,184337,184537,184538,184539,184540,184541,184543,184550,184574,184576,184577,184579,184581,184582,184584,184636,184637,184692,184693,184698,184699,184700,184705,184706,184708,184727,184746,184754,184792,184836,184837,184888,184891,184893,184897,184925,184999,185021,185031,185032,185175,185223,185321,185398,185423,185435,185497,185527,185540,185559,185560,185561,185565,185576,185591,185768,185877,185944,185959,185969,186009,186017,186018,186020,186101,186116,186148,186154,186155,186156,186158,186159,186160,186162,186163,186228,186251,186253,186263,186264,186267,186294,186300,186341,186348,186361,186406,186465,186471,186474,186475,186476,186481,186485,186486,186488,186489,186492,186500,186504,186507,186510,186511,186562,186566,186658,186659,186665,186666,186667,186676,186690,186691,186739,186741,186743,186779,186780,186782,186783,186794,186859,186891,186895,186906,186913,186915,186916,186917,186918,186919,186920,186923,186924,186925,187021,187038,187046,187091,187092,187096,187107,187191,187201,187204,187205,187221,187226,187240,187256,187257,187259,187260,187262,187265,187268,187273,187276,187285,187297,187299,187301,187308,187312,187317,187321,187328,187337,187338,187343,187344,187354,187362,187367,187369,187370,187375,187378,187385,187386,187390,187393,187394,187397,187398,187409,187416,187420,187422,187427,187428,187433,187437,187439,187440,187441,187444,187445,187448,187449,187450,187456,187481,187497,187507,187511,187514,187521,187525,187534,187536,187539,187545,187546,187550,187551,187554,187563,187568,187569,187570,187582,187584,187585,187586,187589,187599,187602,187606,187613,187617,187623,187644,187645,187650,187654,187664,187687,187696,187697,187703,187712,187728,187737,187741,187747,187772,187773,187777,187780,187799,187802,187804,187810,187813,187815,187818,187820,187822,187823,187831,187838,187855,187856,187877,187878,187879,187880,187885,187892,187894,187895,187896,187899,187901,187902,187905,187906,187907,187926,187928,187929,187932,187936,187938,187948,187949,187950,187952,187954,187955,187968,187970,187971,187973,187977,187979,187993,187995,187997,187998,188010,188035,188103,188104,188111,188115,188120,188137,188141,188149,188155,188172,188258,188264,188265,188278,188282,188327,188431,188432,188437,188558,188559,188607,188609,188610,188619,188622,188627,188640,188663,188682,188746,188759,188929,189054,189117,189121,189140,189143,189179,189187,189202,189219,189223,189233,189239,189241,189242,189244,189324,189366,189538,189614,189639,189683,189715,189718,189906,189907,189912,189977,190213,190224,190228,190233,190262,190369,190380,190392,190393,190398,190400,190417,190418,190419,190425,190435,190451,190462,190467,190470,190475,190485,190487,190488,190494,190497,190513,190515,190518,190523,190525,190545,190552,190554,190559,190565,190566,190567,190568,190577,190579,190580,190581,190582,190584,190586,190587,190593,190594,190596,190599,190605,190610,190621,190635,190641,190647,190648,190657,190658,190667,190668,190669,190674,190685,190696,190714,190723,190736,190745,190746,190750,190752,190753,190764,190766,190767,190768,190771,190794,190796,190797,190802,190803,190817,190822,190831,190832,190836,190852,190867,190871,190876,190892,190905,190911,190976,190988,191018,191025,191073,191077,191100,191103,191105,191114,191118,191134,191135,191136,191144,191147,191156,191163,191172,191176,191179,191187,191196,191200,191205,191207,191213,191222,191223,191238,191239,191458,191460,191463,191492,191505,191507,191515,191525,191529,191530,191532,191543,191553,191641,191660,191698,191708,191709,191715,191750,191764,191775,191794,191880,191895,191896,191899,191900,191901,191902,191903,191904,191920,191927,191928,191929,191932,191933,191934,191935,191938,191954,191956,191959,191961,191974,191981,191985,191995,191997,192001,192002,192003,192007,192010,192012,192013,192014,192017,192018,192019,192020,192022,192026,192035,192037,192039,192050,192053,192058,192066,192076,192078,192082,192083,192084,192085,192087,192088,192092,192094,192096,192102,192114,192115,192118,192131,192138,192140,192142,192149,192154,192158,192164,192172,192174,192183,192188,192198,192201,192202,192209,192227,192249,192258,192270,192286,192296,192297,192306,192309,192315,192316,192322,192326,192328,192337,192338,192340,192341,192343,192345,192357,192360,192364,192365,192368,192374,192383,192385,192388,192408,192438,192441,192442,192445,192457,192460,192461,192466,192467,192471,192485,192487,192489,192495,192496,192503,192518,192521,192525,192528,192531,192539,192540,192545,192547,192548,192553,192557,192565,192568,192572,192579,192584,192585,192588,192596,192598,192599,192600,192602,192609,192610,192612,192613,192614,192615,192617,192618,192619,192620,192622,192628,192630,192634,192638,192644,192651,192656,192657,192662,192664,192665,192666,192672,192679,192680,192689,192696,192720,192723,192724,192725,192726,192727,192731,192740,192752,192762,192770,192771,192774,192776,192788,192789,192794,192798,192802,192804,192806,192809,192810,192814,192818,192819,192826,192830,192834,192848,192849,192859,192860,192865,192866,192868,192869,192870,192881,192885,192886,192887,192889,192890,192891,192892,192893,192896,192900,192910,192911,192915,192916,192918,192922,192923,192928,192932,192936,192940,192943,192956,192975,192979,192982,192983,192989,192992,193020,193031,193033,193035,193042,193045,193046,193052,193060,193061,193074,193076,193086,193092,193099,193100,193103,193108,193127,193135,193139,193140,193141,193142,193145,193148,193151,193156,193158,193165,193166,193173,193176,193177,193178,193179,193180,193181,193182,193186,193188,193191,193192,193195,193198,193205,193207,193210,193214,193218,193221,193224,193227,193229,193244,193246,193247,193248,193249,193253,193262,193268,193280,193287,193296,193299,193304,193305,193312,193313,193318,193337,193350,193354,193357,193364,193365,193366,193368,193369,193384,193397,193401,193402,193403,193404,193406,193419,193421,193423,193425,193433,193445,193446,193447,193451,193457,193467,193468,193472,193487,193490,193491,193492,193503,193506,193513,193524,193531,193539,193547,193552,193560,193563,193569,193577,193584,193586,193588,193589,193590,193591,193595,193596,193600,193603,193606,193610,193611,193612,193617,193623,193626,193628,193645,193646,193661,193662,193664,193668,193671,193672,193673,193674,193681,193682,193683,193685,193687,193689,193700,193704,193715,193721,193723,193734,193735,193741,193743,193745,193760,193765,193769,193776,193785,193791,193792,193793,193794,193795,193796,193801,193802,193805,193829,193831,193839,193842,193845,193849,193853,193858,193866,193873,193887,193898,193905,193916,193919,193920,193921,193928,193939,193953,193957,193966,193967,193972,193974,193997,193998,193999,194003,194010,194017,194023,194028,194033,194035,194036,194040,194042,194043,194044,194051,194062,194064,194067,194069,194071,194074,194079,194087,194088,194089,194090,194094,194097,194104,194108,194117,194148,194149,194150,194151,194153,194154,194155,194168,194173,194174,194178,194183,194186,194189,194190,194204,194208,194214,194215,194218,194224,194231,194232,194242,194247,194251,194259,194263,194264,194266,194268,194272,194280,194281,194289,194293,194317,194319,194320,194321,194326,194334,194335,194336,194341,194347,194350,194355,194357,194362,194365,194378,194379,194391,194396,194397,194404,194407,194421,194432,194444,194447,194453,194460,194467,194468,194472,194476,194477,194485,194486,194487,194491,194501,194509,194510,194514,194526,194529,194532,194538,194541,194542,194557,194561,194565,194570,194584,194589,194601,194604,194621,194639,194650,194651,194652,194662,194663,194668,194674,194678,194683,194725,194728,194759,194763,194769,194776,194783,194787,194810,194817,194820,194821,194840,194847,194855,194858,194864,194921,194922,194925,194929,194931,194949,194957,194958,194959,194960,194963,194965,194966,194968,194974,194976,194983,194984,194985,194987,195000,195001,195011,195017,195022,195025,195036,195038,195040,195042,195043,195047,195049,195050,195051,195052,195053,195065,195068,195069,195079,195096,195100,195119,195122,195128,195135,195140,195156,195157,195158,195172,195173,195179,195181,195189,195191,195214,195227,195230,195235,195236,195239,195241,195244,195248,195253,195261,195263,195265,195276,195279,195284,195288,195304,195317,195318,195321,195352,195389,195406,195416,195421,195440,195445,195447,195451,195459,195461,195463,195467,195470,195473,195478,195479,195496,195499,195500,195501,195529,195532,195533,195545,195546,195547,195550,195555,195556,195557,195558,195560,195562,195570,195571,195573,195577,195583,195587,195594,195601,195603,195612,195615,195617,195619,195625,195635,195642,195647,195648,195650,195653,195663,195664,195667,195668,195674,195676,195679,195681,195690,195698,195711,195717,195720,195750,195757,195763,195764,195773,195785,195789,195792,195805,195810,195819,195821,195828,195834,195838,195839,195843,195851,195852,195859,195862,195863,195866,195867,195885,195889,195902,195913,195926,195931,195948,195954,195956,195977,195978,195983,195994,196018,196019,196025,196045,196046,196050,196053,196067,196072,196080,196086,196089,196092,196107,196126,196151,196155,196162,196166,196177,196180,196185,196186,196189,196190,196196,196197,196201,196206,196222,196225,196226,196238,196252,196269,196282,196303,196304,196319,196331,196332,196335,196345,196346,196347,196349,196350,196351,196354,196356,196358,196359,196360,196361,196362,196365,196366,196368,196369,196370,196371,196375,196396,196408,196412,196413,196420,196428,196436,196444,196447,196458,196465,196474,196482,196489,196525,196527,196532,196533,196543,196553,196555,196561,196574,196581,196587,196593,196628,196629,196632,196635,196647,196652,196653,196667,196675,196680,196682,196683,196691,196692,196704,196726,196729,196743,196744,196747,196755,196756,196763,196764,196766,196771,196775,196779,196790,196797,196798,196806,196808,196820,196821,196837,196843,196849,196865,196873,196884,196887,196904,196906,196936,196937,196951,196952,196954,196955,196964,196969,196984,196989,196993,196997,197008,197010,197020,197025,197026,197040,197071,197074,197087,197090,197093,197097,197100,197104,197106,197113,197120,197131,197132,197141,197142,197145,197146,197156,197167,197176,197181,197196,197201,197205,197214,197216,197218,197221,197224,197253,197256,197260,197262,197272,197280,197281,197284,197285,197286,197296,197297,197302,197307,197321,197335,197336,197355,197361,197374,197375,197386,197411,197413,197418,197434,197440,197444,197466,197467,197470,197491,197492,197495,197497,197535,197547,197551,197560,197571,197584,197589,197590,197591,197606,197607,197611,197625,197631,197635,197645,197652,197668,197670,197677,197684,197690,197691,197692,197696,197698,197725,197728,197731,197737,197744,197746,197748,197749,197750,197751,197752,197753,197755,197756,197759,197760,197761,197762,197763,197764,197765,197767,197768,197769,197770,197772,197774,197777,197778,197781,197783,197790,197791,197803,197805,197806,197817,197819,197820,197823,197831,197832,197834,197835,197837,197840,197841,197843,197847,197849,197862,197866,197871,197872,197874,197875,197876,197877,197879,197881,197882,197884,197887,197891,197895,197897,197898,197902,197903,197906,197907,198118,198119,198123,198127,198128,198131,198132,198141,198142,198157,198158,198160,198161,198165,198173,198189,198191,198199,198208,198211,198213,198214,198215,198216,198218,198219,198221,198224,198226,198232,198233,198234,198235,198237,198241,198242,198248,198253,198257,198261,198263,198264,198266,198271,198283,198284,198289,198296,198299,198300,198306,198315,198320,198322,198533,198536,198539,198548,198560,198561,198563,198566,198574,198579,198582,198585,198592,198599,198602,198603,198609,198610,198617,198629,198632,198633,198634,198637,198638,198639,198641,198642,198643,198652,198658,198659,198665,198671,198674,198676,198683,198690,198698,198700,198708,198721,198724,198730,198733,198734,198950,198957,198958,198960,198964,198965,198969,198970,198971,198972,198974,198977,198988,198994,198997,199002,199004,199005,199008,199009,199014,199016,199020,199021,199023,199026,199028,199031,199032,199033,199034,199035,199036,199037,199038,199039,199040,199041,199043,199044,199045,199046,199047,199048,199050,199051,199053,199054,199057,199058,199059,199060,199061,199062,199063,199064,199065,199066,199067,199068,199069,199071,199072,199073,199077,199078,199079,199080,199081,199082,199083,199084,199085,199086,199087,199088,199093,199094,199096,199097,199100,199102,199104,199106,199108,199109,199110,199111,199113,199114,199116,199117,199118,199120,199121,199122,199123,199124,199125,199126,199127,199128,199129,199130,199131,199133,199134,199135,199137,199140,199144,199147,199150,199152,199155,199161,199162,199165,199166,199167,199169,199170,199174,199176,199179,199181,199182,199183,199189,199192,199195,199202,199214,199216,199217,199220,199221,199222,199223,199227,199229,199231,199233,199234,199242,199245,199248,199249,199250,199251,199252,199253,199254,199255,199263,199269,199271,199273,199277,199281,199283,199284,199286,199287,199288,199289,199293,199297,199301,199303,199304,199305,199306,199317,199321,199323,199324,199325,199331,199332,199334,199339,199341,199348,199351,199355,199358,199360,199361,199362,199363,199367,199374,199376,199381,199385,199387,199389,199390,199391,199392,199394,199402,199403,199405,199408,199411,199413,199420,199422,199424,199428,199438,199439,199442,199450,199451,199460,199461,199462,199465,199466,199467,199477,199478,199480,199481,199485,199487,199488,199489,199494,199496,199500,199513,199515,199519,199520,199522,199529,199534,199542,199549,199552,199554,199562,199569,199570,199575,199584,199591,199593,199595,199597,199599,199602,199605,199606,199609,199614,199615,199617,199620,199624,199625,199627,199629,199635,199638,199641,199642,199643,199644,199651,199655,199657,199660,199661,199662,199663,199665,199667,199677,199683,199689,199692,199694,199695,199703,199710,199718,199722,199724,199725,199726,199731,199733,199735,199737,199740,199741,199742,199759,199770,199771,199772,199775,199778,199782,199783,199784,199786,199787,199797,199799,199801,199813,199820,199825,199829,199830,199831,199837,199838,199839,199841,199843,199844,199850,199857,199858,199862,199872,199878,199879,199890,199891,199903,199906,199916,199922,199929,199941,199943,199946,199955,199961,199967,199971,199973,199976,199977,199978,199980,199983,199984,199992,200199,200202,200204,200205,200208,200209,200210,200217,200220,200221,200226,200235,200237,200238,200239,200240,200243,200247,200248,200252,200253,200254,200256,200258,200259,200260,200261,200262,200263,200265,200266,200277,200280,200283,200284,200295,200296,200297,200300,200306,200310,200312,200313,200315,200316,200317,200321,200323,200324,200329,200330,200332,200333,200334,200337,200338,200341,200346,200347,200348,200351,200352,200353,200355,200358,200360,200362,200363,200366,200368,200370,200372,200376,200377,200378,200379,200382,200387,200389,200395,200396,200398,200400,200402,200403,200404,200405,200408,200410,200411,200417,200419,200421,200422,200425,200426,200431,200433,200437,200443,200446,200447,200457,200459,200474,200475,200477,200479,200480,200481,200483,200490,200497,200498,200502,200506,200509,200510,200512,200513,200514,200523,200526,200528,200534,200542,200543,200544,200547,200548,200549,200551,200559,200569,200571,200572,200574,200575,200576,200583,200585,200589,200590,200591,200595,200596,200598,200607,200609,200619,200620,200621,200622,200623,200624,200631,200632,200642,200645,200656,200657,200658,200666,200669,200675,200679,200685,200686,200691,200693,200694,200695,200697,200698,200699,200701,200705,200714,200719,200721,200724,200725,200726,200740,200751,200763,200765,200771,200775,200784,200787,200794,200796,200797,200799,200800,200802,200806,200807,200809,200810,200811,200812,200817,200822,200830,200835,200840,200846,200847,200850,200851,200852,200855,200856,200858,200863,200864,200866,200867,200869,200875,200882,200883,200885,200888,200891,200894,200901,200902,200908,200911,200912,200914,200915,200921,200926,200928,200931,200939,200951,200962,200965,200971,200973,200987,200998,201004,201011,201018,201021,201023,201036,201049,201053,201054,201058,201060,201061,201062,201069,201085,201099,201103,201107,201113,201117,201122,201124,201130,201133,201143,201150,201156,201157,201170,201186,201187,201191,201198,201209,201216,201228,201241,201246,201257,201263,201273,201277,201287,201288,201307,201314,201320,201330,201333,201335,201337,201351,201358,201367,201385,201390,201399,201430,201437,201438,201443,201453,201471,201476,201479,201486,201499,201500,201507,201509,201512,201517,201518,201533,201542,201557,201574,201575,201576,201578,201581,201582,201583,201584,201585,201586,201587,201588,201589,201590,201591,201594,201595,201596,201602,201605,201606,201607,201622,201624,201628,201629,201642,201643,201650,201664,201676,201689,201691,201693,201694,201699,201703,201714,201715,201717,201725,201743,201746,201748,201755,201761,201766,201784,201790,201793,201796,201797,201798,201799,201800,201802,201807,201808,201809,201819,201823,201826,201830,201831,201839,201848,201865,201866,201870,201872,202051,202130,202152)));
//->addFieldToFilter('entity_id',array('in' => array(146757)));

$correctedCountEmail = 0;
$failedCountEmail = 0;
$correctedCountName= 0;
$failedEmailArray = array();
foreach ($customerCollection as $customerOb) {
    $customer = Mage::getModel('customer/customer')->load($customerOb->getId());
    if($customer){
        $prefix = $customer->getPrefix();
        $fName = $customer->getFirstname();
        $mName = $customer->getMiddlename();
        $lName = $customer->getLastname();
        $fullName = "";

        $sql = "";
        if ($prefix) {
            $fullName .= $prefix . " ";
        }
        $fullName .= $fName . " ";
        if ($mName) {
            $fullName .= $mName;
        }
        $fullName .= $lName;
        $email = $customer->getEmail();
        $formattedEmail = $emailFormatterHelper->startMailFormating($email,$fullName,null);
        if (strlen($fName) < 1 || strlen($lName) < 1) {
            $log = "Change customer name from {$fullName}} to = ";
            $fullName = explode("@", $formattedEmail, 2)[0];
            $log = $log. $fullName;
            echo $log;
            echo "</br>";
            //$customer->setData('lastname',$fullName);
            $correctedCountName++;
        }



        if ($formattedEmail != $email) {
            try{
                //$customer->setData('email',$formattedEmail);
                //$customer->save();
                $log = "";
                $log = "Change custome email from {$email} to {$formattedEmail}";
                echo $log;
                echo "</br>";
                $correctedCountEmail++;
            }catch (Exception $e) {
                //$helper->salesforceLog("Error for Customer - {$customer->getId()} with message {$e->getMessage()}");
                $failedCountEmail++;
                array_push($failedEmailArray,$customer->getId());
            }
        }
    }
}
//$helper->salesforceLog("Corrected email Count - {$correctedCountEmail}");
echo "Corrected email Count - {$correctedCountEmail}";echo "</br>";
//$helper->salesforceLog("Failed email Count - {$failedCountEmail}");
echo "Failed email Count - {$failedCountEmail}";echo "</br>";

//$helper->salesforceLog("Corrected name Count - {$correctedCountName}");
echo "Corrected name Count - {$correctedCountName}";echo "</br>";

//$helper->salesforceLog(print_r($failedEmailArray));
print_r($failedEmailArray);
die;
//$sql = "";
//$fullName="latthe";
//$formattedEmail = 'indrajeet@allureinc.co';
//$email = 'indrajeet1@allureinc.co';
//$id = 146757;
//$sql = $sql. "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,7,{$id},'{$fullName}');";
//
//if($formattedEmail != $email) {
//    //$sql = $sql. "REPLACE INTO `customer_entity`(entity_id,attribute_set_id,entity_type_id,website_id,email,) VALUES(1,9,{$id},'{$formattedEmail}');";
//    $sql = $sql. "UPDATE `customer_entity` SET email = '{$formattedEmail}' WHERE entity_id = {$id};";
//}
//
//echo $sql;
$helper = Mage::helper("allure_salesforce/emailFormatter");
$customer = Mage::getModel('customer/customer')->load(146757);
if ($customer) {
    $customerId = $customer->getId();
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $emailFormatterHelper = Mage::helper('allure_salesforce/emailFormatter');
    //$this->salesforceLog("Customer {$$customerId}");

    $salesforceId = $customer->getSalesforceCustomerId();
    $salesforceContactId = $customer->getSalesforceContactId();

//    if ($create && (!empty($salesforceId) && !empty($salesforceContactId))) {
//        $this->salesforceLog("Tried to create Customer and Contact - " . $customerId . ". But Customer and Contact already Present in SF -" . $salesforceId);
//        return;
//    }
//
//    if (!$create && (empty($salesforceId) || empty($salesforceContactId))) {
//        $this->salesforceLog("Tried to update Customer or Contact - " . $customerId . ". But Customer or Contact not Present in SF -" . $salesforceId);
//        return;
//    }

    $prefix = $customer->getPrefix();
    $fName = $customer->getFirstname();
    $mName = $customer->getMiddlename();
    $lName = $customer->getLastname();
    $fullName = "";

    $sql = "";
    if ($prefix) {
        $fullName .= $prefix . " ";
    }
    $fullName .= $fName . " ";
    if ($mName) {
        $fullName .= $mName;
    }
    $fullName .= $lName;
    $email = $customer->getEmail();
    $formattedEmail = $emailFormatterHelper->startMailFormating($email, $fullName, null);
    //echo $customerId."-".strlen($fName).":".strlen($lName)."</br>";
    //if (strlen($fName) < 1 || strlen($lName) < 1) {
        $fullName = explode("@", $formattedEmail, 2)[0];
        //$this->salesforceLog("Tried to get data of Customer or Contact - " . $$customerId . ". But Customer or Contact Doesn't have any name-" . $salesforceId . "Changed name too = " . $fullName);
        $sql = $sql . "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,7,{$customerId},'{$fullName}');";
    //}

    if ($formattedEmail != $email) {
        //$sql = $sql. "REPLACE INTO `customer_entity_varchar`(entity_type_id,attribute_id,entity_id,value) VALUES(1,9,{$customerId},'{$formattedEmail}');";
        $sql = $sql . "UPDATE `customer_entity` SET email = '{$formattedEmail}' WHERE entity_id = {$customerId};";
    }
    var_dump($sql);
}
die;

//$helper = Mage::helper("allure_salesforce/salesforceClient");






$email = $helper->startMailFormating("ALICENAM@GM,AIL.COM","asd",null);
$email = $helper->startMailFormating($customer->getEmail(),$fullName,null);
var_dump($email);die;


die;
$model = Mage::getModel('allure_salesforce/observer_update');
$arr = array(48572,18087,18688,48691,48693,18982,18981,34111,34112,18244,18239,18234,18229,18224,48722,18715,20097,48460,48458,48463,48459,48462,48461,48471,43357,12189,12188,12187,12183,4954,4955,4956,4957,4958,4959,4960,4961,4962,4963,4964,4965,4966,4967,4968,31923,32002,22737,40302,40303,40304,34278,34309,48905,48904,48902,48903);
$sRequest = array();
foreach($arr as $ar) {
    $product = Mage::getModel('catalog/product')->load($ar);
    $salesforceProductId = $product->getSalesforceProductId();
    $wholesalePrice = 0;
    foreach ($product->getData('group_price') as $gPrice) {
        if ($gPrice["cust_group"] == 2) { //wholesaler group : 2
            $wholesalePrice = $gPrice["price"];
        }
    }

    $sTemp = array(
        "attributes" => array(
            "type" => "PricebookEntry",
            "referenceId" => "productW-" . $product->getId()
        ),
        "Pricebook2Id" => Mage::helper('allure_salesforce')->getWholesalePricebook(),//$this::WHOLESELLER_PRICEBOOK_ID,
        "Product2Id" => $salesforceProductId,
        "UnitPrice" => $wholesalePrice
    );
    array_push($sRequest, $sTemp);
}
//$model->sendCompositeRequest(array("products" => $sRequest),null);
$requestData = array("pb" => $sRequest);
$objectMappings = array(
    "pb" => "PriceBookEntry"
);
foreach ($requestData as $modelName => $reqArr) {
    if (!empty($reqArr)) {
        $chunkedReqArray = array_chunk($reqArr, 200);
        foreach ($chunkedReqArray as $reqArray) {
            $request["records"] = $reqArray;

            if (empty($lastRunTime)) {
                $urlPath = "/services/data/v42.0/composite/tree/" . $objectMappings[$modelName];
                $requestMethod = "POST";
            }
            print_r(json_encode($request,true));die;
            $response = $helper->sendRequest($urlPath, $requestMethod, $request);
            $responseArr = json_decode($response, true);

            if (!$responseArr["hasErrors"]) {
                $helper->salesforceLog("bulk operation was succesfull");
                $helper->addSalesforcelogRecord("BULK operation ", $requestMethod, "BULKOP-" . $lastRunTime, $response);
                if (empty($lastRunTime))
                    $helper->bulkProcessResponse($responseArr, $modelName);
            }
        }
    }
}
print_r($sRequest);die;

//$order = Mage::getModel('sales/order')->load(437297);
//var_dump($order->getCouponCode());
//
//var_dump($order->getCouponRuleName());
//
//
//die;

$product = Mage::getModel('catalog/product')->load(49132);
$stoneWeightClassification = $product->getData('stone_weight_classification');
$barcode = $product->getData('barcode');
var_dump($barcode);die;
var_dump($product->getData('weight'));die;

//$sd = Mage::getModel('eav/attribute_option_value')->load(1129);

$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
    ->setAttributeFilter(238)
    ->setStoreFilter(0)
    ->load();

$s = $_collection->toOptionArray();
foreach($s as $t) {
    if($t['value'] == $product->getData('custom_stock_status')) {
        var_dump($t['label']);
    }
}

die;




$_customer = Mage::getModel('customer/customer')->load(189547);

//$subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($_customer);
//var_dump($subscriber->isSubscribed());die;
//$_customer->setConfirmation(1);
//$_customer->save();die;
//var_dump($_customer->getCreatedAt());die;

$collection = Mage::getResourceModel('sales/sale_collection')
    ->setCustomerFilter($_customer)
    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true)
    ->load()
;
var_dump($collection->getTotals()->getBaseLifetime());
var_dump($collection->getTotals()->getAvgsale());
die;
if (!$_customer->getConfirmation()) {
    var_dump(Mage::helper('customer')->__('Confirmed'));echo "adada";
}

if ($_customer->isConfirmationRequired()) {
    var_dump(Mage::helper('customer')->__('Not confirmed, cannot login'));echo "bsda";
}

var_dump(Mage::helper('customer')->__('Not confirmed, can login'));echo "ccsada";

die;

$config = "allure_salesforce/general/bulk_cron_time";
var_dump(Mage::getStoreConfig($config));die;

//$requestData = array(
//    "orders" => array(454144,454145),
//    "order_items" => array(737038,737039),
//    "customers" => array(186464),
//    "contact" => array(186464),
//    "invoice" => array(499066,499067),
//    "credit_memo" => array(22535,22536),
//    "shipment" => array(329824,329823),
//);

$requestData = array(
    "orders" => array(454158),
);

$lastRunTime = new DateTime("1 hour ago");  //static right now only for test purpose
//print_r($lastRunTime);die;
$model = Mage::getModel("allure_salesforce/observer_update");
$model->getRequestData(null,$requestData);
//$model->getRequestData($lastRunTime,null);
die;

//$model = Mage::getModel("allure_salesforce/observer_update");
//$data = $model->getRequestData();
//echo "<pre>";
//print_r($data,true);
//die;