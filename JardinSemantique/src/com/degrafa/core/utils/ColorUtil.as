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
	* A helper utility class for color conversion.
	**/
	public class ColorUtil{
				
		/**
		* Converts Color Keys to color values. If the specified color key is not 
		* supported then black is used.
		**/
		public static function colorKeyToDec(value:String):uint{
			
			var colorKey:String = value.toUpperCase();
			
			if(ColorKeys[colorKey]){
				return uint(ColorKeys[colorKey]);
			}
			else{
				return 0x000000;	
			}	
		}
		
		
		/**
		* Conversion from CMYK to RGB values.
		**/   
		public static function cmykToDec(value:String):uint{   
			var tempColors:Array = value.split(",");
			
			var cyan:int = (0xFF * tempColors[0])/100;         
			var magenta:int = (0xFF * tempColors[1])/100;         
			var yellow:int = (0xFF * tempColors[2])/100;         
			var key:int = (0xFF * tempColors[3])/100;              
			
			var red:int = int(decColorToHex(Math.round((0xFF-cyan)*(0xFF-key)/0xFF)));         
			var green:int = int(decColorToHex(Math.round((0xFF-magenta)*(0xFF-key)/0xFF)));         
			var blue:int = int(decColorToHex(Math.round((0xFF-yellow)*(0xFF-key)/0xFF)));          
		
			return uint(rgbToDec(red +","+ green +","+blue));
					
		}
		
		
		
		/**
		* Converts an RGB percentage comma separated list 
		* to a decimal color value. Expected order is R,G,B.
		**/
		public static function rgbPercentToDec(value:String):uint{
			
			//Note: Should be verified for rounding differences
			//depending on the industry norms
						
			var tempColors:Array = value.replace(/%/g,"").split(",");
								
			//convert as a percentage of 0 to 255 before the bitwise op
			return uint((int(Math.round(tempColors[0]/100*255))<<16) | (int(Math.round(tempColors[1]/100*255))<< 8) | int(Math.round(tempColors[2]/100*255)));
			
		}
		
		/**
		* Converts an RGB numeric comma separated list
		* to a decimal color value. Expected order R,G,B.
		**/
		public static function rgbToDec(value:String):Number{
			
			var tempColors:Array = value.split(",");
			return uint((int(tempColors[0])<<16) | (int(tempColors[1])<< 8) | int(tempColors[2]));
			
		}
		
		/**
		* Converts a HEX color value to an RGB value object.
		**/
		public static function hexToRgb(hex:Number):Object{
			var red:Number = hex>>16;        
			var greenBlue:Number = hex-(red<<16);        
			var green:Number = greenBlue>>8;        
			var blue:Number = greenBlue-(green<<8);        
			return({red:red, green:green, blue:blue})
		}
		
		/**
		* Converts a decimal color to a hex value.
		**/
		public static function decColorToHex(color:Number):String{
			
			var colorArray:Array = color.toString(16).toUpperCase().split('');
			var numChars:Number = colorArray.length;
			
			var i:int
			for(i=0;i<(6-numChars);i++){
				colorArray.unshift("0");
			}
			
			return('0x' + colorArray.join(''));
				
		}
		
		/**
		* Take a short color notation and convert it to a full color.
		* Repeats each value once so that #FB0 expands to #FFBB00 
		**/
		public static function parseColorNotation(color:uint):uint{

			//break this into an array to work with 
			var repeatArray:Array = color.toString(16).split("");
			
			//repeat and cast it back
			repeatArray[0] += repeatArray[0];
			repeatArray[1] += repeatArray[1];
			repeatArray[2] += repeatArray[2];
			
			return uint("0x" + repeatArray.join(""));
				
		}
		
	}
}