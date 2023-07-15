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
	import com.degrafa.core.IGraphicsFill;
	
	import flash.display.Graphics;
	import flash.geom.Matrix;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
	
	[DefaultProperty("gradientStops")]
	[Exclude(name="color", kind="property")]
	[Exclude(name="alpha", kind="property")]
	
	[Bindable(event="propertyChange")]
	/**
	* The gradient fill class lets you specify a gradient fill. 
	* This is the base class for other gradient fill types.
	* 
	* @see http://degrafa.com/samples/LinearGradientFill.html	  
	**/
	public class GradientFill extends SolidFill implements IGraphicsFill{
		
		public function GradientFill(){
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
			for each(var gradientStop:GradientStop in _gradientStops.items){	
				
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
			_colors[changedIndex]= value.color;
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
		* @see mx.graphics.LinearGradient 
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
		* @see mx.graphics.LinearGradient 
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
		* @see mx.graphics.LinearGradient 
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
		
		private var _gradientUnits:String = "objectBoundingBox";
		[Inspectable(category="General", enumeration="userSpaceOnUse,objectBoundingBox", defaultValue="objectBoundingBox")]
		/**
		* Gradient coordinate system for attributes.
		**/
		public function get gradientUnits():String{
			return _gradientUnits;
		}
		public function set gradientUnits(value:String):void{			
			if(_gradientUnits != value){
				var oldValue:String=_gradientUnits;
			
				_gradientUnits = value;
			
				//call local helper to dispatch event	
				initChange("gradientUnits",oldValue,_gradientUnits,this);
			}
			
		}	
		
		/**
		* Ends the fill for the graphics context.
		* 
		* @param graphics The current context being drawn to.
		**/
		override public function end(graphics:Graphics):void{
			graphics.endFill();
		}
		
		/**
		* Begins the fill for the graphics context.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds.  
		**/
		override public function begin(graphics:Graphics, rc:Rectangle):void{
			var matrix:Matrix;
			
			if (rc)
			{
				matrix=new Matrix();
				matrix.createGradientBox(rc.width, rc.height,
				(_angle/180)*Math.PI, rc.x, rc.y);
				var xp:Number = (_angle % 90)/90;
				var yp:Number = 1 - xp;
				//var temp:Matrix = new Matrix();
				//temp.rotate((_angle/180)*Math.PI);
				//var point:Point = temp.transformPoint(new Point(rectangle.width, 0));
				processEntries(rc.width*xp + rc.height*yp);
			}
			else
			{
				matrix = null;
			}			
			
			graphics.beginGradientFill(_gradientType,_colors,_alphas,_ratios,matrix,_spreadMethod,_interpolationMethod);
					
		}
		
		/**
		* Process the gradient stops 
		**/
		private function processEntries(length:Number):void{
			_colors = [];
			_ratios = [];
			_alphas = [];
			
			if (!_gradientStops || _gradientStops.items.length == 0){return;}
			
			var ratioConvert:Number = 255;
			
			var i:int;
			
			var n:int = _gradientStops.items.length;
			for (i = 0; i < n; i++) {
				var e:GradientStop = _gradientStops.items[i];
				_colors.push(e.color);
				_alphas.push(e.alpha);
				if(e.measure != null && e.measure.value >= 0) {
					var ratio:Number = e.measure.relativeTo(length)/length;
					_ratios.push(Math.min(ratio, 1) * ratioConvert);
				} else {
					_ratios.push(NaN);
				}
			}
			
			if (isNaN(_ratios[0]))
				_ratios[0] = 0;
				
			if (isNaN(_ratios[n - 1]))
				_ratios[n - 1] = 255;
			
			i = 1;
			
			while (true) {
				while (i < n && !isNaN(_ratios[i])) {
					i++;
				}
				
				if (i == n)
					break;
				
				var start:int = i - 1;
				
				while (i < n && isNaN(_ratios[i])) {
					i++;
				}
				
				var br:Number = _ratios[start];
				var tr:Number = _ratios[i];
				
				for (var j:int = 1; j < i - start; j++) {
					_ratios[j] = br + j * (tr - br) / (i - start);
				}
			}
		}
		
	}
}