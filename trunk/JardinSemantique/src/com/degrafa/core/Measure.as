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
package com.degrafa.core
{
	
	import flash.system.Capabilities;
	
	public class Measure
	{
		
		// relative length units
		public static const EM:String = "em";
		public static const EX:String = "ex";
		public static const PIXELS:String = "pixels";
		//public static const GRID:String = "gd";
		//public static const REM:String = "rem";
		//public static const VIEWPORT_WIDTH:String = "vw";
		//public static const VIEWPORT_HEIGHT:String = "vh";
		//public static const VIEWPORT_MEASURE:String = "vm";
		public static const CH:String = "ch";
		public static const PERCENT:String = "percent"; // 0 to 100
		public static const RATIO:String = "ratio"; // 0 to 1
		
		// absolute length units
		public static const INCHES:String = "inches";
		public static const CENTIMETERS:String = "centimeters";
		public static const MILLIMETERS:String = "millimeters";
		public static const POINTS:String = "points";
		public static const PICAS:String = "picas";
		
		// alternative
		public static const DEGREES:String = "degrees";
		
		// todo: implement real font measurement?
		public var emSize:Number = -1;
		public var exSize:Number = -1;
		public var chSize:Number = -1;
		
		public var unit:String;
		public var value:Number;
		
		public function Measure(value:Number = 0, unit:String = Measure.PIXELS):void {
			this.value = value;
			this.unit = unit;
		}
		
		public function relativeTo(length:Number, object:Object = null):Number {
			switch(unit) {
				case Measure.PIXELS:
					return value;
					break;
				case Measure.PERCENT:
					return length*(value/100);
					break;
				case Measure.RATIO:
					return length*value;
					break;
				case Measure.POINTS:
					return (value/72)*Capabilities.screenDPI;
					break;
				case Measure.INCHES:
					return value*Capabilities.screenDPI;
					break;
				case Measure.CENTIMETERS:
					return (value/2.54)*Capabilities.screenDPI;
					break;
				case Measure.MILLIMETERS:
					return (value/25.4)*Capabilities.screenDPI;
					break;
				case Measure.PICAS:
					return (value/6)*Capabilities.screenDPI;
					break;
				case Measure.EM:
					updateFontSizes();
					return value*emSize;
					break;
				case Measure.EX:
					updateFontSizes();
					return value*exSize;
					break;
				case Measure.CH:
					updateFontSizes();
					return value*chSize;
					break;
				default:
					return value;
					break;
			}
		}
		
		private function updateFontSizes():void {
			if(emSize < 0) {
				emSize = (12/72)*Capabilities.screenDPI; // 12pt?
			}
			if(exSize < 0) {
				exSize = (6/72)*Capabilities.screenDPI; // half line of 12pt?
			}
			if(chSize < 0) {
				chSize = (12/72)*Capabilities.screenDPI; // 12pt?
			}
		}
		
		
		//Object Cohersion Methods
		
		public function toString():String {
			return value.toString() + unit;
		}
		
		public function valueOf():Number {
			return relativeTo(100);
		}
		
	}
}