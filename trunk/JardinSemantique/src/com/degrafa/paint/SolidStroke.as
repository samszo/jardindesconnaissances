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
	
	import com.degrafa.core.IGraphicsStroke;
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.utils.ColorUtil;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
		
	[Bindable(event="propertyChange")]
	
	/**
 	* The Stroke class defines the properties for a line. You can define a 
 	* Stroke object in MXML, but you must attach that Stroke to another object 
 	* for it to appear in your application.
 	* 
 	* @see mx.graphics.Stroke
 	* @see http://samples.degrafa.com/SolidStroke/SolidStroke.html  
 	**/ 
	public class SolidStroke extends DegrafaObject implements IGraphicsStroke{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The solid stroke constructor accepts 3 optional arguments that define 
	 	* it's rendering color, alpha and weight.</p>
	 	* 
	 	* @param color A unit value indicating the stroke color.
	 	* @param alpha A number indicating the alpha to be used for the stoke.
	 	* @param weight A number indicating the weight of the line for the stroke. 
	 	*/		
		public function SolidStroke(color:uint=0x000000, alpha:Number=1,weight:Number=1):void{
						
			this.color=color;
			this.alpha=alpha;
			this.weight=weight;
			
		}
						
		private var _weight:Number=1;
		[Inspectable(category="General", defaultValue=1)]
		/**
 		* The line weight, in pixels.
 		* 
 		* @see mx.graphics.Stroke
 		**/ 
		public function get weight():Number{
			return _weight;
		}
		public function set weight(value:Number):void{
			if(_weight != value){
				var oldValue:Number=_weight;
			
				_weight = value;
			
				//call local helper to dispatch event	
				initChange("weight",oldValue,_weight,this);
			}
			
		}
				
		private var _scaleMode:String = "normal";
		[Inspectable(category="General", enumeration="normal,vertical,horizontal,none", defaultValue="normal")]
		/**
 		* Specifies how to scale a stroke.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get scaleMode():String{
			return _scaleMode;
		}
		public function set scaleMode(value:String):void{			
			if(_scaleMode != value){
				var oldValue:String=_scaleMode;
			
				_scaleMode = value;
			
				//call local helper to dispatch event	
				initChange("joints",oldValue,_scaleMode,this);
			}
		}
			
		private var _pixelHinting:Boolean = false;
		[Inspectable(category="General")]
		/**
 		* Specifies whether to hint strokes to full pixels.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get pixelHinting():Boolean{
			return _pixelHinting;
		}
		public function set pixelHinting(value:Boolean):void{						
			if(_pixelHinting != value){
				var oldValue:Boolean=_pixelHinting;
			
				_pixelHinting = value;
			
				//call local helper to dispatch event	
				initChange("pixelHinting",oldValue,_pixelHinting,this);
			}			
		}
						
		private var _miterLimit:Number = 3;
		[Inspectable(category="General")]
		/**
 		* Indicates the limit at which a miter is cut off.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get miterLimit():Number{
			return _miterLimit;
		}
		public function set miterLimit(value:Number):void{			
			if(_miterLimit != value){
				var oldValue:Number=_miterLimit;
			
				_miterLimit = value;
			
				//call local helper to dispatch event	
				initChange("miterLimit",oldValue,_miterLimit,this);
			}
			
		}
				
		private var _joints:String = "round";
		[Inspectable(category="General", enumeration="round,bevel,miter", defaultValue="round")]
		/**
 		* Specifies the type of joint appearance used at angles.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get joints():String{
			return _joints;
		}
		public function set joints(value:String):void{
			
			if(_joints != value){
				var oldValue:String=_joints;
			
				_joints = value;
			
				//call local helper to dispatch event	
				initChange("joints",oldValue,_joints,this);
			}
			
		}
				
		private var _color:uint = 0x000000;
		[Inspectable(category="General", format="Color",defaultValue="0x000000")]
		/**
 		* The line color.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get color():uint{
			return _color;
		}
		public function set color(value:uint):void{
			
			if(_color != value){
				var oldColor:uint=_color;
				
				//short notation assumption 
				if (value.toString(16).length==3){
					_color = ColorUtil.parseColorNotation(value);		
				}
				else{
					_color=value;
				}
							
				//call local helper to dispatch event	
				initChange("color",oldColor,_color,this);
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
			if (value.search("%")!=-1){
				color=ColorUtil.rgbPercentToDec(value);	
			}
			else{
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
				
		private var _caps:String = "round";
		[Inspectable(category="General", enumeration="round,square,none", defaultValue="round")]
		/**
 		* Specifies the type of caps at the end of lines.
 		* 
 		* @see mx.graphics.Stroke
 		**/
		public function get caps():String{
			return _caps;
		}
		public function set caps(value:String):void{
			if(_caps != value){
				var oldValue:String=_caps;
			
				_caps = value;
			
				//call local helper to dispatch event	
				initChange("caps",oldValue,_caps,this);
			}
			
		}
				
		private var _alpha:Number=1;
		[Inspectable(category="General")]
		/**
 		* The transparency of a line.
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
		
		
		/**
 		* Applies the properties to the specified Graphics object.
 		* 
 		* @see mx.graphics.Stroke
 		* 
 		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for stroke bounds. 
 		**/
		public function apply(graphics:Graphics,rc:Rectangle):void{
			
			graphics.lineStyle(_weight, _color, _alpha, _pixelHinting,
					_scaleMode, _caps, _joints, _miterLimit);
					
		}
		
	}
}