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
package com.degrafa.paint{
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.utils.ColorUtil;
	import com.degrafa.core.IGraphicsFill;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	[Bindable(event="propertyChange")]
	
	/**
	* Solid fill defines a fill color to be applied to a graphics contex.
	* 
	* @see http://samples.degrafa.com/SolidFill/SolidFill.html  
	**/
	public class SolidFill extends DegrafaObject implements IGraphicsFill{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The solid fill constructor accepts 2 optional arguments that define 
	 	* it's rendering color and alpha.</p>
	 	* 
	 	* @param color A unit value indicating the stroke color.
	 	* @param alpha A number indicating the alpha to be used for the fill.
	 	*/
		public function SolidFill(color:uint=0x000000, alpha:Number=1):void{
			
			_color=color;
			_alpha=alpha;
			
		}
		
		private var _alpha:Number=1;
		[Inspectable(category="General")]
		/**
 		* The transparency of a fill.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get alpha():Number{
			return _alpha;
		}
		public function set alpha(value:Number):void{
			if(_alpha != value){
				var oldValue:Number=_alpha;
			
				_alpha = value;
			
				//call local helper to dispatch event	
				initChange("alpha",oldValue,_alpha,this);
			}
		}
				
		private var _color:uint = 0x000000;
		[Inspectable(category="General", format="Color",defaultValue="0x000000")]
		/**
 		* The fill color.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get color():uint{
			return _color;
		}
		public function set color(value:uint):void{
			if(_color != value){
				var oldValue:Number=_color;
			
				//short notation assumption 
				if (value.toString(16).length==3)
				{
					_color = ColorUtil.parseColorNotation(value);		
				}
				else
				{
					_color=value;
				}
			
				//call local helper to dispatch event	
				initChange("color",oldValue,_color,this);
			}
		}
		
		private var _colorKey:String;
		[Inspectable(category="General", format="string")]
		/**
		* Allows a constant string value for example azure. 
		* See ColorKeys for list of available values.
		**/
		public function get colorKey():String{
			return _colorKey;
		}
		public function set colorKey(value:String):void
		{		
			_colorKey=value;
			
			//translate the keyword to a color value and apply 
			//the result to the color property
			color=ColorUtil.colorKeyToDec(value);
			
		}
		
		private var _rgbColor:String;
		[Inspectable(category="General", format="string")]
		/**
		* Allows an comma-separated list of three numerical or 
		* percent values that are then converted to a hex value. 
		**/
		public function get rgbColor():String{
			return _rgbColor;
		}
		public function set rgbColor(value:String):void{		
			_rgbColor=value;
			
			//check and see if it is a percent list or a numeric list
			if (value.search("%")!=-1)
			{
				color=ColorUtil.rgbPercentToDec(value);	
			}
			else
			{
				color=ColorUtil.rgbToDec(value);	
			}
			
		}
				
		private var _cmykColor:String;
		[Inspectable(category="General", format="string")]
		/**
		* Allows an comma-separated list of 4 numerical
		* values that represent cmyk and are then converted to 
		* a decimal color value.
		**/
		public function get cmykColor():String{
			return _cmykColor;
		}
		public function set cmykColor(value:String):void{		
			_cmykColor=value;
			
			color=ColorUtil.cmykToDec(value);	
			
		}
		
		/**
		* Ends the fill for the graphics context.
		* 
		* @param graphics The current context being drawn to.
		**/
		public function end(graphics:Graphics):void{
			graphics.endFill();
		}
		
		/**
		* Begins the fill for the graphics context.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds.  
		**/
		public function begin(graphics:Graphics, rc:Rectangle):void{			
			graphics.beginFill(_color,_alpha);						
		}
		
	}
}