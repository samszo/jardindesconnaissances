////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2008 Jason Hawryluk, Juan Sanchez, Andy McIntosh, Ben Stucki 
// and Pavan Podila.
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
////////////////////////////////////////////////////////////////////////////////

package com.degrafa.core.utils{
	/**
	* A list of colorKey name constants that respect the SVG 
	* specification of ColorKeys.
	**/
	public final class ColorKeys{
		
		public static const ALICEBLUE:uint = 0xF0F8FF;//240,248,255
		public static const ANTIQUEWHITE:uint = 0xFAEBD7;//250,235,215
		public static const AQUA:uint = 0x00FFFF;//0,255,255
		public static const AQUAMARINE:uint = 0x7FFFD4;//127,255,212
		public static const AZURE:uint = 0xF0FFFF;//240,255,255
		public static const BEIGE:uint = 0xF5F5DC;//245,245,220
		public static const BISQUE:uint = 0xFFE4C4;//255,228,196
		public static const BLACK:uint = 0x000000;//0,0,0
		public static const BLANCHEDALMOND:uint = 0xFFEBCD;//255,235,205
		public static const BLUE:uint = 0x0000FF;//0,0,255
		public static const BLUEVIOLET:uint = 0x8A2BE2;//138,43,226
		public static const BROWN:uint = 0xA52A2A;//165,42,42
		public static const BURLYWOOD:uint = 0xDEB887;//222,184,135
		public static const CADETBLUE:uint = 0x5F9EA0;//95,158,160
		public static const CHARTREUSE:uint = 0x7FFF00;//127,255,0
		public static const CHOCOLATE:uint = 0xD2691E;//210,105,30
		public static const CORAL:uint = 0xFF7F50;//255,127,80
		public static const CORNFLOWERBLUE:uint = 0x6495ED;//100,149,237
		public static const CORNSILK:uint = 0xFFF8DC;//255,248,220
		public static const CRIMSON:uint = 0xDC143C;//220,20,60
		public static const CYAN:uint = 0x00FFFF;//0,255,255
		public static const DARKBLUE:uint = 0x00008B;//0,0,139
		public static const DARKCYAN:uint = 0x008B8B;//0,139,139
		public static const DARKGOLDENROD:uint = 0xB8860B;//184,134,11
		public static const DARKGRAY:uint = 0xA9A9A9;//169,169,169
		public static const DARKGREEN:uint = 0x006400;//0,100,0
		public static const DARKGREY:uint = 0xA9A9A9;//169,169,169
		public static const DARKKHAKI:uint = 0xBDB76B;//189,183,107
		public static const DARKMAGENTA:uint = 0x8B008B;//139,0,139
		public static const DARKOLIVEGREEN:uint = 0x556B2F;//85,107,47
		public static const DARKORANGE:uint = 0xFF8C00;//255,140,0
		public static const DARKORCHID:uint = 0x9932CC;//153,50,204
		public static const DARKRED:uint = 0x8B0000;//139,0,0
		public static const DARKSALMON:uint = 0xE9967A;//233,150,122
		public static const DARKSEAGREEN:uint = 0x8FBC8F;//143,188,143
		public static const DARKSLATEBLUE:uint = 0x483D8B;//72,61,139
		public static const DARKSLATEGRAY:uint = 0x2F4F4F;//47,79,79
		public static const DARKSLATEGREY:uint = 0x2F4F4F;//47,79,79
		public static const DARKTURQUOISE:uint = 0x00CED1;//0,206,209
		public static const DARKVIOLET:uint = 0x9400D3;//148,0,211
		public static const DEEPPINK:uint = 0xFF1493;//255,20,147
		public static const DEEPSKYBLUE:uint = 0x00BFFF;//0,191,255
		public static const DIMGRAY:uint = 0x696969;//105,105,105
		public static const DIMGREY:uint = 0x696969;//105,105,105
		public static const DODGERBLUE:uint = 0x1E90FF;//30,144,255
		public static const FIREBRICK:uint = 0xB22222;//178,34,34
		public static const FLORALWHITE:uint = 0xFFFAF0;//255,250,240
		public static const FORESTGREEN:uint = 0x228B22;//34,139,34
		public static const FUCHSIA:uint = 0xFF00FF;//255,0,255
		public static const GAINSBORO:uint = 0xDCDCDC;//220,220,220
		public static const GHOSTWHITE:uint = 0xF8F8FF;//248,248,255
		public static const GOLD:uint = 0xFFD700;//255,215,0
		public static const GOLDENROD:uint = 0xDAA520;//218,165,32
		public static const GRAY:uint = 0x808080;//128,128,128
		public static const GREEN:uint = 0x008000;//0,128,0
		public static const GREENYELLOW:uint = 0xADFF2F;//173,255,47
		public static const GREY:uint = 0x808080;//128,128,128
		public static const HONEYDEW:uint = 0xF0FFF0;//240,255,240
		public static const HOTPINK:uint = 0xFF69B4;//255,105,180
		public static const INDIANRED:uint = 0xCD5C5C;//205,92,92
		public static const INDIGO:uint = 0x4B0082;//75,0,130
		public static const IVORY:uint = 0xFFFFF0;//255,255,240
		public static const KHAKI:uint = 0xF0E68C;//240,230,140
		public static const LAVENDER:uint = 0xE6E6FA;//230,230,250
		public static const LAVENDERBLUSH:uint = 0xFFF0F5;//255,240,245
		public static const LAWNGREEN:uint = 0x7CFC00;//124,252,0
		public static const LEMONCHIFFON:uint = 0xFFFACD;//255,250,205
		public static const LIGHTBLUE:uint = 0xADD8E6;//173,216,230
		public static const LIGHTCORAL:uint = 0xF08080;//240,128,128
		public static const LIGHTCYAN:uint = 0xE0FFFF;//224,255,255
		public static const LIGHTGOLDENRODYELLOW:uint = 0xFAFAD2;//250,250,210
		public static const LIGHTGRAY:uint = 0xD3D3D3;//211,211,211
		public static const LIGHTGREEN:uint = 0x90EE90;//144,238,144
		public static const LIGHTGREY:uint = 0xD3D3D3;//211,211,211
		public static const LIGHTPINK:uint = 0xFFB6C1;//255,182,193
		public static const LIGHTSALMON:uint = 0xFFA07A;//255,160,122
		public static const LIGHTSEAGREEN:uint = 0x20B2AA;//32,178,170
		public static const LIGHTSKYBLUE:uint = 0x87CEFA;//135,206,250
		public static const LIGHTSLATEGREY:uint = 0x778899;//119,136,153
		public static const LIGHTSTEELBLUE:uint = 0xB0C4DE;//176,196,222
		public static const LIGHTYELLOW:uint = 0xFFFFE0;//255,255,224
		public static const LIME:uint = 0x00FF00;//0,255,0
		public static const LIMEGREEN:uint = 0x32CD32;//50,205,50
		public static const LINEN:uint = 0xFAF0E6;//250,240,230
		public static const MAGENTA:uint = 0xFF00FF;//255,0,255
		public static const MAROON:uint = 0x800000;//128,0,0
		public static const MEDIUMAQUAMARINE:uint = 0x66CDAA;//102,205,170
		public static const MEDIUMBLUE:uint = 0x0000CD;//0,0,205
		public static const MEDIUMORCHID:uint = 0xBA55D3;//186,85,211
		public static const MEDIUMPURPLE:uint = 0x9370DB;//147,112,219
		public static const MEDIUMSEAGREEN:uint = 0x3CB371;//60,179,113
		public static const MEDIUMSLATEBLUE:uint = 0x7B68EE;//123,104,238
		public static const MEDIUMSPRINGGREEN:uint = 0x00FA9A;//0,250,154
		public static const MEDIUMTURQUOISE:uint = 0x48D1CC;//72,209,204
		public static const MEDIUMVIOLETRED:uint = 0xC71585;//199,21,133
		public static const MIDNIGHTBLUE:uint = 0x191970;//25,25,112
		public static const MINTCREAM:uint = 0xF5FFFA;//245,255,250
		public static const MISTYROSE:uint = 0xFFE4E1;//255,228,225
		public static const MOCCASIN:uint = 0xFFE4B5;//255,228,181
		public static const NAVAJOWHITE:uint = 0xFFDEAD;//255,222,173
		public static const NAVY:uint = 0x000080;//0,0,128
		public static const OLDLACE:uint = 0xFDF5E6;//253,245,230
		public static const OLIVE:uint = 0x808000;//128,128,0
		public static const OLIVEDRAB:uint = 0x6B8E23;//107,142,35
		public static const ORANGE:uint = 0xFFA500;//255,165,0
		public static const ORANGERED:uint = 0xFF4500;//255,69,0
		public static const ORCHID:uint = 0xDA70D6;//218,112,214
		public static const PALEGOLDENROD:uint = 0xEEE8AA;//238,232,170
		public static const PALEGREEN:uint = 0x98FB98;//152,251,152
		public static const PALETURQUOISE:uint = 0xAFEEEE;//175,238,238
		public static const PALEVIOLETRED:uint = 0xDB7093;//219,112,147
		public static const PAPAYAWHIP:uint = 0xFFEFD5;//255,239,213
		public static const PEACHPUFF:uint = 0xFFDAB9;//255,218,185
		public static const PERU:uint = 0xCD853F;//205,133,63
		public static const PINK:uint = 0xFFC0CB;//255,192,203
		public static const PLUM:uint = 0xDDA0DD;//221,160,221
		public static const POWDERBLUE:uint = 0xB0E0E6;//176,224,230
		public static const PURPLE:uint = 0x800080;//128,0,128
		public static const RED:uint = 0xFF0000;//255,0,0
		public static const ROSYBROWN:uint = 0xBC8F8F;//188,143,143
		public static const ROYALBLUE:uint = 0x4169E1;//65,105,225
		public static const SADDLEBROWN:uint = 0x8B4513;//139,69,19
		public static const SALMON:uint = 0xFA8072;//250,128,114
		public static const SANDYBROWN:uint = 0xF4A460;//244,164,96
		public static const SEAGREEN:uint = 0x2E8B57;//46,139,87
		public static const SEASHELL:uint = 0xFFF5EE;//255,245,238
		public static const SIENNA:uint = 0xA0522D;//160,82,45
		public static const SILVER:uint = 0xC0C0C0;//192,192,192
		public static const SKYBLUE:uint = 0x87CEEB;//135,206,235
		public static const SLATEBLUE:uint = 0x6A5ACD;//106,90,205
		public static const SLATEGRAY:uint = 0x708090;//112,128,144
		public static const SLATEGREY:uint = 0x708090;//112,128,144
		public static const SNOW:uint = 0xFFFAFA;//255,250,250
		public static const SPRINGGREEN:uint = 0x00FF7F;//0,255,127
		public static const STEELBLUE:uint = 0x4682B4;//70,130,180
		public static const TAN:uint = 0xD2B48C;//210,180,140
		public static const TEAL:uint = 0x008080;//0,128,128
		public static const THISTLE:uint = 0xD8BFD8;//216,191,216
		public static const TOMATO:uint = 0xFF6347;//255,99,71
		public static const TURQUOISE:uint = 0x40E0D0;//64,224,208
		public static const VIOLET:uint = 0xEE82EE;//238,130,238
		public static const WHEAT:uint = 0xF5DEB3;//245,222,179
		public static const WHITE:uint = 0xFFFFFF;//255,255,255
		public static const WHITESMOKE:uint = 0xF5F5F5;//245,245,245
		public static const YELLOW:uint = 0xFFFF00;//255,255,0
		public static const YELLOWGREEN:uint = 0x9ACD32;//154,205,50

	}
}