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
package com.degrafa.geometry.segment{
	
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.IDegrafaObject;
	
	import flash.geom.Rectangle;
	
	[DefaultProperty("data")]
	[Bindable(event="propertyChange")]	
	
	/**
	* Base class for segment elements that make up path geometry.
	**/ 
	public class Segment extends DegrafaObject implements IDegrafaObject{
		
		/**
		* Specifies whether this object is to be re calculated 
		* on the next cycle.
		**/
		public var invalidated:Boolean;
		
		/**
		* Performs any pre calculation that is required to successfully render 
		* this element. Including bounds calculations and lower level drawing 
		* command storage. Each geometry object overrides this 
		* and is responsible for it's own pre calculation cycle.
		**/
		public function preDraw():void{
			//overridden
		}
		
		private var _data:String;
		/**
		* Allows a short hand property setting that is 
		* specific to and parsed by each geometry object. 
		* Look at the various geometry objects to learn what 
		* this setting requires.
		**/	
		public function get data():String{
			return _data;
		}
		public function set data(value:String):void{
			_data=value;
		}
		
		/**
		* Used for short sequence svg support specifically in a quad or 
		* cubic instance where the the mirror of the last control point 
		* is to be used as the new see svg specification S,s,T,t
		**/
		private var _isShortSequence:Boolean;
		public function get isShortSequence():Boolean{
			return _isShortSequence;
		}
		public function set isShortSequence(value:Boolean):void{
			if(_isShortSequence != value){
				_isShortSequence = value;
			}
			
		}
		
		
		/**
		* Coordinate type to be used for segment.
		**/
		private var _coordinateType:String="absolute";
		[Inspectable(category="General", enumeration="absolute,relative", defaultValue="absolute")]
		public function set coordinateType(value:String):void{
			if(_coordinateType != value){
				_coordinateType = value;
			}
		}
		public function get coordinateType():String{
			return _coordinateType;
		}
		
		//strickly overriden for each segment type
		/**
		* Returns this segment type.
		**/
		public function get segmentType():String
		{
			return "none";
		}
				
	}
}