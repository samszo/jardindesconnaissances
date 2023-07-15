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
	
	import com.degrafa.core.Measure;
	import com.degrafa.paint.BitmapFill;
	import com.degrafa.paint.ComplexFill;
	import com.degrafa.paint.GradientStop;
	import com.degrafa.paint.LinearGradientFill;
	
	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.DisplayObject;
	import flash.filters.DropShadowFilter;
	import flash.geom.Point;
	import flash.utils.Dictionary;
	
	import mx.graphics.IFill;
	import mx.graphics.SolidColor;
	
	/**
	 * A helper utility class for style processing.
	 */
	public class StyleUtil{
		
		private static var functions:Dictionary;
		
		
		//********************************************
		// Public Methods
		//********************************************
		
		/*
		public static function resolveStroke(value:Object, none:IStroke = null):IStroke {
			if(value is IStroke) {
				return IStroke(value);
			} else if(value is uint) {
				return new Stroke(0, uint(value));
			} else if(value is String) {
				return resolveStrokeFromString(value);
			} else {
				return none;
			}
		}
		*/
		
		/**
		 * Return an IFill object based on the CSS input given.
		 * @param value May be a uint, String, asset, or Array of uints, Strings, or assets.
		 */
		public static function resolveFill(value:Object, none:IFill = null):IFill {
			if(value is uint) {
				return new SolidColor(value as uint);
			} else if(value is Class || value is BitmapData || value is Bitmap || value is DisplayObject) { // causes duplicate type checking
				//var instance:DisplayObject = new (value as Class);
				//if(instance is Bitmap) {
					return new BitmapFill(value);
				//} else {return none;}
			} else if(value is String) {
				return resolveFillFromString(value as String);
			} else if(value is Array) {
				return resolveFillFromArray(value as Array);
				//return resolveBitmapFromArray(value as Array);
			} else if(value is IFill) {
				return value as IFill;
			} else {
				return none;
			}
		}
		
		public static function resolveRepeat(value:Object, none:Array = null):Array {
			if(value is String) {
				return [resolveRepeatFromString(value as String)]
			} else if(value is Array) {
				return resolveRepeatFromArray(value as Array);
			}
			return none;
		}
		
		public static function resolvePosition(value:Object, none:Array = null):Array {
			if(value is String) {
				return [resolvePositionFromString(value as String)];
			} else if(value is Array) {
				return resolvePositionFromArray(value as Array);
			} else {
				return none;
			}
		}
		
		/**
		 * Returns an array of DropShadowFilter objects based on the CSS input given.
		 * @parameter value May be a Number, String or Array of Number or Strings
		 */
		public static function resolveShadow(value:Object, none:Array = null ):Array {
			if(value is Array) {
				return resolveShadowFromArray(value as Array);
			} else if(value is String || value is Number || value is int || value is uint) {
				return [resolveShadowFromString(value.toString())];
			} else {
				return none;
			}
			return result;
		}
		
		public static function resolveRadius(value:Object, none:Point = null):Object {
			if(value is Number) {
				return {x:new Measure(Number(value)), y:new Measure(Number(value))};
			} else {
				// should be none
				return  {x:new Measure(0), y:new Measure(0)};
			}
		}
		
		public static function resolveMeasure(value:Object, none:Measure = null):Measure {
			if(value is Number || value is int || value is uint) {
				return new Measure(value as Number);
			} else if(value is String) {
				return resolveMeasureFromString(value as String);
			} else {
				return none;
			}
		}
		
		public static function registerFunction(name:String, f:Function):void {
			if(functions == null) {
				functions = new Dictionary();
			}
			functions[name] = f;
		}
		
		
		//*******************************
		// Parsing Functions
		//*******************************
		
		/*
		private static function resolveStrokeFromString(value:String):IStroke {
			var properties:Array = expandProperty(value, [0, "solid", 0]);
			var test:uint = colorFromStyle(properties[0]);
			if(properties[1] is uint) { properties[2] = properties[1]; }
			if(properties[0] is String && test == 0) { properties[1] = properties[0]; }
			else if(properties[0] is String && test > 0 && isNaN(properties[0])) { properties[2] = properties[1]; }
			return new Stroke(colorFromStyle(properties[2]), Number(properties[0]));
			//return {width: Number(array[0]), style: String(array[1]), color: resolveColor(array[2])};
		}
		*/
		
		private static function resolveFillFromArray(value:Array):IFill {
			var complex:ComplexFill = new ComplexFill();
			var fills:Array = complex.fills = new Array();
			for each(var object:Object in value as Array) {
				var fill:IFill = resolveFill(object);
				fills.push(fill);
			}
			return complex;
		}
		
		// this whole thing needs refactoring
		private static function resolveFillFromString(value:String):IFill {
			if(value == null) { return null; }
			var color:uint = 0;
			if(value.indexOf(" ") > 0) { // gradient definitions must have at least one space
				return resolveGradientFromString(value);
			}
			if(String(value).charAt(0)=="#" || String(value).substr(0,2)=="0x") {
				color = parseInt(String(value).replace("#", ""), 16);
			} else { color = parseInt(String(value), 10); }
			if(isNaN(color) || color==0) {
				var token:String = String(value).toUpperCase();
				color = ColorKeys[token];
			}
			return new SolidColor(color);
		}
		
		private static function resolveGradientFromString(value:String):IFill {
			var fill:LinearGradientFill = new LinearGradientFill();
			fill.gradientStops = new Array();
			var split:Array = splitString(value);
			var length:int = split.length;
			if(length > 0) {
				var i:int = 0;
				var angle:Measure = resolveMeasure(split[0]);
				if(angle != null && angle.unit == Measure.DEGREES) {
					fill.angle = angle.value;
					i = 1;
				}
				while(i < length) {
					var entry:GradientStop = new GradientStop();
					
					var measure:Measure = resolveMeasureFromString(split[i]);
					if(measure != null) {
						entry.ratio = measure.value;
						entry.ratioUnit = measure.unit;
						i++
					}
					
					var alpha:Measure = resolveMeasureFromString(split[i]);
					if(alpha != null) {
						entry.alpha = Number(alpha);
					}
					
					var color:IFill = resolveFillFromString(split[i]);
					if(color != null && color is SolidColor) {
						entry.color = (color as SolidColor).color;
						i++;
					}
					fill.gradientStops.push(entry);
					//fill.entries = [new MeasuredGradientEntry(0xFF0000, new Measure(0, "px")), new MeasuredGradientEntry(0x0000FF, new Measure(100, "px"))];
				}
			}
			return fill;
		}
		
		/*
		private static function resolveBitmapFromArray(value:Array):IFill {
			var complex:ComplexFill = new ComplexFill();
			var fills:Array = complex.fills = new Array();
			for each(var object:Object in value as Array) {
				var fill:IFill = resolveBitmap(object);
				fills.push(fill);
			}
			return complex;
		}
		*/
		private static function resolveRepeatFromString(value:String):Object {
			switch(value) {
				case "repeat":
					return {x:"repeat", y:"repeat"};
					break;
				case "repeat-x":
					return {x:"repeat", y:"none"};
					break;
				case "repeat-y":
					return {x:"none", y:"repeat"};
					break;
				case "no-repeat":
					return {x:"none", y:"none"};
					break;
				case "space":
					return {x:"space", y:"space"};
					break;
				case "stretch":
					return {x:"stretch", y:"stretch"};
					break;
				default:
					return {x:"repeat", y:"repeat"};
					break;
			}
		}
		
		private static function resolveRepeatFromArray(value:Array):Array {
			var result:Array = new Array();
			for each(var item:Object in value) {
				if(item is String) {
					result.push(resolveRepeatFromString(item as String));
				} else {
					result.push(item);
				}
			}
			return result;
		}
		
		private static function resolvePositionFromString(value:String):Object {
			var properties:Array = expandProperty(value, ["top", -1]);
			return {x:resolveMeasureFromString(properties[1] as String), y:resolveMeasureFromString(properties[0] as String)};
		}
		
		private static function resolvePositionFromArray(value:Array):Array {
			var result:Array = new Array();
			for each(var item:Object in value) {
				if(item is String) {
					result.push(resolvePositionFromString(item as String));
				} else {
					result.push(item);
				}
			}
			return result;
		}
		
		private static function resolveMeasureFromString(value:String):Measure {
			if(value == null) { return null; }
			switch(value) {
				case "bottom":
				case "right":
					return new Measure(100, Measure.PERCENT);
					break;
				case "top":
				case "left":
					return new Measure(0, Measure.PIXELS);
					break;
				case "center":
					return new Measure(50, Measure.PERCENT);
					break;
			}
			value = value.replace(" ", "");
			var length:int = value.length;
			for(var i:int = 0; i < length; i++) {
				var ch:String = value.charAt(i);
				
				switch(ch) {
					case "0":
					case "1":
					case "2":
					case "3":
					case "4":
					case "5":
					case "6":
					case "7":
					case "8":
					case "9":
					case "-":
						break;
					default:
						var v:Number = Number(value.substring(0, i));
						var u:String = value.substring(i);
						switch(u) {
							case "px":
								return new Measure(v, Measure.PIXELS);
								break;
							case "pt":
								return new Measure(v, Measure.POINTS);
								break;
							case "%":
								return new Measure(v, Measure.PERCENT);
								break;
							case "deg":
								return new Measure(v, Measure.DEGREES);
								break;
							case "in":
								return new Measure(v, Measure.INCHES);
								break;
							default:
								return null;
								break;
						}
				}
			}
			return new Measure(Number(value));
		}
		
		private static function resolveShadowFromArray(value:Array):Array {
			var filters:Array = new Array();
			var i:int = value.length;
			while(i-->0) {
				filters.push( resolveShadow(value[i]) );
			}
			return filters;
		}
		
		private static function resolveShadowFromString(value:String):DropShadowFilter {
			var result:DropShadowFilter = new DropShadowFilter(0, 0, 0, 1, 0, 0);
			var array:Array = expandProperty(value, [0, -1, 0, 0] );
			var point:Point = new Point(resolveLength(array[0]), resolveLength(array[1]));
			result.distance = Math.sqrt(Math.pow(point.x, 2) + Math.pow(point.y, 2));
			result.angle = Math.atan2(point.y, point.x)*(180/Math.PI);
			result.blurX = result.blurY = resolveLength(array[2]);
			result.color = (resolveFill(array[3]) as SolidColor).color;
			return result;
		}
		
		private static function resolveLength( object:Object ):Number {
			return Number( object );
		}
		
		
		//*******************************
		// generic utility functions
		//*******************************
		
		/**
		 * Expands shorthand properties into an Array of values.
		 * This is used to evaluate shorthand CSS properties where ommited values may
		 * have a default value or be inherited from other values defined by the property.
		 * @argument property A shorthand property declaration (usually a String).
		 * @argument relationships An array of values used to determine the value of ommited values in the property.
		 * <ul>
		 *  <li>A negative integer defines that the default value should be inherited from a value to the left.</li>
		 *  <li>All other values define an explicit default.</li>
		 * </ul>
		 */
		public static function expandProperty(property:Object, relationships:Array):Array {
			var split:Array = splitString(property.toString());
			var length:int = relationships.length;
			for(var i:int = split.length; i < length; i++) {
				var v:Object = relationships[i];
				if(v is int && v < 0) {
					split.push(split[i+v]);
				} else {
					split.push(v);
				}
			}
			return split;
		}
		
		/**
		 * Converts space seperated values into an Array.
		 */
		public static function splitString( value:String ):Array {
			var split:Array = value.split(" ");
			for(var i:uint = 0; i < split.length; i++) {
				if(split[i]==""){
					split.splice(i,1);
					i--;
				}
			}
			return split;
		}
		
		private static function isFunction(value:String):Boolean {
			return false;
		}
		
		
	}
}