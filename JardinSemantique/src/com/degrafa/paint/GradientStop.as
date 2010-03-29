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
	import com.degrafa.core.Measure;
	
	[Bindable(event="propertyChange")]  
	/**
	* The gradient stop class defines the objects that control 
	* a transition as part of a gradient fill.
	* 
	* @see mx.graphics.GradientEntry  
	**/
	public class GradientStop extends DegrafaObject{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The solid stroke constructor accepts 3 optional arguments that define 
	 	* it's rendering color, alpha and weight.</p>
	 	* 
	 	* @param color A unit value indicating the stop color.
	 	* @param alpha A number indicating the alpha to be used for the stop.
	 	* @param ratio A number indicating where in the graphical element, as a percentage from 0.0 to 1.0, Flex starts the transition to the associated color.
	 	* @param ratioUnit A string indicating the unit of the ratio for the stop.  
	 	*/	
		public function GradientStop(color:uint=0x000000,alpha:Number=1,ratio:Number=-1,ratioUnit:String="ratio"){
			_color = color;
			_alpha = alpha;
			_ratio.value = ratio;
			_ratio.unit = ratioUnit;
		}
	    
	    private var _alpha:Number = 1;
		[Inspectable(category="General", defaultValue="1")]
		/**
		* The transparency of a gradient fill.
		*
		* @see mx.graphics.GradientEntry
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
				
		private var _color:uint=0x000000;
		[Inspectable(category="General", format="color")]
		/**
		* The color value for a gradient stop.
		*
		* @see mx.graphics.GradientEntry
		**/
		public function get color():uint{
			return _color;
		}
		public function set color(value:uint):void{	
			if(_color != value){
				var oldValue:Number=_color;
											
				//short notation assumption 
				if (value.toString(16).length==3){
					_color = ColorUtil.parseColorNotation(value);		
				}
				else{
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
		*
		* @see com.degrafa.core.utils.ColorKeys  
		**/
		public function get colorKey():String{
			return _colorKey;
		}
		public function set colorKey(value:String):void{		
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
		public function set cmykColor(value:String):void
		{		
			_cmykColor=value;
			
			color=ColorUtil.cmykToDec(value);	
			
		}
		
				    
		private var _ratio:Measure = new Measure(-1, Measure.RATIO);
		[Inspectable(category="General")]
		/**
		* Where in the graphical element, as a percentage from 0.0 to 1.0, 
		* Flex starts the transition to the associated color.
		*
		* @see mx.graphics.GradientEntry
		**/
		public function get ratio():Number{
			return _ratio.value;
		}
		public function set ratio(value:Number):void
		{		
			if(_ratio.value != value){
				var oldValue:Number=_ratio.value;
			
				_ratio.value = value;
			
				//call local helper to dispatch event	
				initChange("ratio",oldValue,_ratio.value,this);
			}
			
		}
		
		[Inspectable(category="General")]
		/**
		* Unit of measure for the ratio of the stop.
		**/
		public function get ratioUnit():String{
			return _ratio.unit;
		}
		public function set ratioUnit(value:String):void{		
			if(_ratio.unit != value){
				var oldValue:String=_ratio.unit;
			
				_ratio.unit = value;
			
				//call local helper to dispatch event	
				initChange("ratio",oldValue,_ratio.unit,this);
			}
			
		}
		
		/**
		* Returns the current ratio unit value.
		**/
		public function get measure():Measure{
			return _ratio;
		}
		
	}

}
