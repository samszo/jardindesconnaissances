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
	
	import com.degrafa.core.IDegrafaObject;
	import com.degrafa.core.collections.GradientStopsCollection;
	
	import flash.display.Graphics;
	import flash.geom.Matrix;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
		
	[DefaultProperty("gradientStops")]
	[Exclude(name="color", kind="property")]
	[Exclude(name="alpha", kind="property")]
	
	[Exclude(name="rgbColor", kind="property")]
	[Exclude(name="cmykColor", kind="property")]
	[Exclude(name="colorKey", kind="property")]
	
	[Bindable(event="propertyChange")]
	
	
	/**
	* The gradient stroke class lets you specify a gradient filled stroke. 
	* This is the base class for other gradient stroke types.
	* 
	* @see http://degrafa.com/samples/LinearGradientStroke.html  
	**/
	public class GradientStroke extends SolidStroke{
		
		public function GradientStroke():void{
			super();
		}
		
		private var _colors:Array = [];
		private var _ratios:Array = [];
		private var _alphas:Array = [];
		
		private var _gradientStops:GradientStopsCollection;
	    [Inspectable(category="General", arrayType="com.degrafa.paint.GradientStop")]
	    [ArrayElementType("com.degrafa.paint.GradientStop")]
		/**
		* A array of gradient stops that describe this gradient.
		**/
		public function get gradientStops():Array{
			if(!_gradientStops){_gradientStops = new GradientStopsCollection();}
			return _gradientStops.items;
		}
		public function set gradientStops(value:Array):void{
			
			if(!_gradientStops){_gradientStops = new GradientStopsCollection();}
			_gradientStops.items = value;
			
			
			//update
			for each(var gradientStop:GradientStop in _gradientStops.items)
			{					
				_colors.push(gradientStop.color);
				_alphas.push(gradientStop.alpha);
				_ratios.push(gradientStop.ratio*255);
			}
			
			//add a listener to the collection
			if(_gradientStops && enableEvents){
				_gradientStops.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
			
			
		}
		
		
		/**
		* Access to the Degrafa gradient stop collection object for this gradient.
		**/
		public function get gradientStopsCollection():GradientStopsCollection{
			if(!_gradientStops){_gradientStops = new GradientStopsCollection();}
			return _gradientStops;
		}
		
		/**
		* Updates the internal array of stops for the index in the items array 
		* that has been changed.
		**/
		private function updateFillProperties(value:GradientStop):void{
			var changedIndex:int = _gradientStops.indexOf(value);
			_colors[changedIndex] = value.color;
			_alphas[changedIndex]= value.alpha;
			_ratios[changedIndex]= value.ratio*255;
		}
		
		/**
		* Principle event handler for any property changes to a 
		* gradient stop or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			
			updateFillProperties(GradientStop(event.source));
			
			dispatchEvent(event);
		}
		
		private var _angle:Number = 0;
		[Inspectable(category="General")]
		/**
		* The angle that defines a transition across the context.
		* 
		* @see mx.graphics.LinearGradientStroke 
		**/
		public function get angle():Number{
			return _angle;
		}
		public function set angle(value:Number):void{
			if(_angle != value){
				var oldValue:Number=_angle;
			
				_angle = value;
			
				//call local helper to dispatch event	
				initChange("angle",oldValue,_angle,this);
			}
			
		}
	
		private var _focalPointRatio:Number = 0;
		[Inspectable(category="General")]
		/**
		* Sets the location of the start of a radial fill.
		* 
		* @see mx.graphics.RadialGradient 
		**/
		public function get focalPointRatio():Number{
			return _focalPointRatio;
		}
		public function set focalPointRatio(value:Number):void{			
			if(_focalPointRatio != value){
				var oldValue:Number=_focalPointRatio;
			
				_focalPointRatio = value;
			
				//call local helper to dispatch event	
				initChange("focalPointRatio",oldValue,_focalPointRatio,this);
			}
			
		}
				
		private var _gradientType:String = "linear";
		[Inspectable(category="General", enumeration="linear,radial", defaultValue="linear")]
		/**
		* Sets the type of gradient to be applied.
		**/
		public function get gradientType():String{
			return _gradientType;
		}
		public function set gradientType(value:String):void{
			if(_gradientType != value){
				var oldValue:String=_gradientType;
			
				_gradientType = value;
			
				//call local helper to dispatch event	
				initChange("gradientType",oldValue,_gradientType,this);
			}
			
		}
			
		private var _spreadMethod:String = "pad";
		[Inspectable(category="General", enumeration="pad,reflect,repeat", defaultValue="pad")]
		/**
		* A value from the SpreadMethod class that specifies which spread method to use.
		* 
		* @see mx.graphics.LinearGradientStroke  
		**/
		public function get spreadMethod():String{
			return _spreadMethod;
		}
		public function set spreadMethod(value:String):void{			
			if(_spreadMethod != value){
				var oldValue:String=_spreadMethod;
			
				_spreadMethod = value;
			
				//call local helper to dispatch event	
				initChange("spreadMethod",oldValue,_spreadMethod,this);
			}
			
		}
				
		private var _interpolationMethod:String = "rgb";
		[Inspectable(category="General", enumeration="rgb,linearRGB", defaultValue="rgb")]
		/**
		* A value from the InterpolationMethod class that specifies which interpolation method to use.
		* 
		* @see mx.graphics.LinearGradientStroke  
		**/
		public function get interpolationMethod():String{
			return _interpolationMethod;
		}
		public function set interpolationMethod(value:String):void{			
			if(_interpolationMethod != value){
				var oldValue:String=_interpolationMethod;
			
				_interpolationMethod = value;
			
				//call local helper to dispatch event	
				initChange("interpolationMethod",oldValue,_interpolationMethod,this);
			}
			
		}
				
		/**
 		* Applies the properties to the specified Graphics object.
 		* 
 		* @see mx.graphics.LinearGradientStroke
 		* 
 		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for stroke bounds. 
 		**/
		override public function apply(graphics:Graphics,rc:Rectangle):void{
			
			//set the base line style properties
			super.apply(graphics,null);
							
			var matrix:Matrix;
			
			if (rc)
			{
				matrix=new Matrix();
				matrix.createGradientBox(rc.width,rc.height,
				(_angle/180)*Math.PI,	rc.x, rc.y);
			}
			else{
				matrix=null;
			}			
			
			graphics.lineGradientStyle(_gradientType,_colors,_alphas,_ratios,matrix,_spreadMethod,_interpolationMethod,_focalPointRatio);
					
		}
	
		
	}
}