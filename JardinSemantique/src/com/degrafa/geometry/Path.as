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
package com.degrafa.geometry{
	
	import com.degrafa.GraphicPoint;
	import com.degrafa.IGeometry;
	import com.degrafa.core.collections.SegmentsCollection;
	import com.degrafa.geometry.segment.*;
	import com.degrafa.geometry.utilities.*;
	
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	import mx.events.PropertyChangeEvent;
	
	[DefaultProperty("segments")]	
	[Bindable]
	
	/**
 	*  The Path element draws a path comprised of several 
 	*  available path commands using the specified path data 
 	*  value.
 	* 
 	*  <p>Paths represent the geometry of the outline of an object, 
	*  defined in terms of moveto (set a new current point), lineto 
	*  (draw a straight line), curveto (draw a curve using a cubic Bézier), 
	*  arc (elliptical or circular arc) and closepath (close the current shape 
	*  by drawing a line to the last moveto) elements.</p>   
 	* 
 	* 
 	*  @see http://www.w3.org/TR/SVG/paths.html    
 	*  @see http://samples.degrafa.com/Path/Path.html
 	* 
 	**/
	public class Path extends Geometry implements IGeometry{
		
		/**
	 	* Constructor.
	 	*  
	 	* <p>The path constructor accepts 1 optional argument that 
	 	* defines it's segments.</p>
	 	* 
	 	* @param data A string representing 1 or more segment commands
	 	* with which to draw the path.
	 	*/	
		public function Path(data:String=null){
			super();
			
			super.data=data;
			
		}
		
		/**
		* Path short hand data value.
		* 
		* <p>The line data property expects exactly 1 value that 
		* defines the path. See below link to SVG path specification 
		* of which we follow most of.</p>
		* 
		* @see Geometry#data
		* @see http://www.w3.org/TR/SVG/paths.html
		* 
		**/	
		override public function set data(value:String):void{
			if(super.data != value){
				super.data = value;
				
				/**
				* Parse the path data using svg commands:
				* ClosePath = z
				* Moveto = M,m
				* LineTo = L,l
				* EllipticalArcTo = A,a
				* HorizontalLineTo = H,h
				* VerticalLineTo = V,v
				* Quadratic Bezier = Q,q,T,t
				* Cubic Bezier = C,c,S,s
				* NOTE: Cubic, and Quadratic will be added 
				**/
				
				var pathDataArray:Array = PathDataToArray(value)
			
				for (var i:int=0;i<pathDataArray.length;i++)
				{
					switch(pathDataArray[i])
					{
						
						case "L":
							
							segments.push(new LineTo(pathDataArray[i+1]+' '+pathDataArray[i+2]));
							
							i+=2;
							
							//if the next item in the array is a number 
							//assume that the line is a continued array
							//so create a new line segment for each point 
							//pair until we get to another item
							if (!isNaN(Number(pathDataArray[i+1])))
							{
								while (!isNaN(Number(pathDataArray[i+1])))
								{
									segments.push(new LineTo(pathDataArray[i+1]
									+' '+pathDataArray[i+2]));
									i+=2;
								}
							}
							
							break;
								
						case "l":
													
							segments.push(new LineTo(pathDataArray[i+1]+' '+pathDataArray[i+2]
							,"relative"));
												
							i+=2;
													
							break;
						case "h":
							
							segments.push(new HorizontalLineTo(pathDataArray[i+1],"relative"));
							
							i+=1;
							break;
						case "H":
								
							segments.push(new HorizontalLineTo(pathDataArray[i+1]));
							
							i+=1;
							break;
						case "v":
													
							segments.push(new VerticalLineTo(pathDataArray[i+1],"relative"));
							
							i+=1;
							break;
						case "V":
																			
							segments.push(new VerticalLineTo(pathDataArray[i+1]));
							
							i+=1;
							break;
						
						case "q":
													
							segments.push(new QuadraticBezierTo(pathDataArray[i+1] + ' ' + 
							pathDataArray[i+2] + ' ' + pathDataArray[i+3] + ' ' + 
							pathDataArray[i+4],"relative"));
													
							i += 4;
							
							break;
							
						case "Q":
						
							segments.push(new QuadraticBezierTo(pathDataArray[i+1] + ' ' + 
							pathDataArray[i+2] + ' ' + pathDataArray[i+3] + ' ' + 
							pathDataArray[i+4]));
							
							i += 4;
							
							break;		
									
						case "t":
						
							segments.push(new QuadraticBezierTo(
							0 + ' ' + 0 +' '+
							pathDataArray[i+1] + ' ' + pathDataArray[i+2],"relative",true))
												
							i += 2;
							break;
						case "T":
						
							segments.push(new QuadraticBezierTo(
							0 + ' ' + 0 +' '+
							pathDataArray[i+1] + ' ' + pathDataArray[i+2],"absolute",true))
							
							i += 2;
							break;
						
						case "c":
												
							segments.push(new CubicBezierTo(
							pathDataArray[i+1] + ' ' + pathDataArray[i+2] +' '+
							pathDataArray[i+3] + ' ' + pathDataArray[i+4] +' '+
							pathDataArray[i+5] + ' ' + pathDataArray[i+6],"relative"))
							
							
							i += 6;
													
							break;
						
						case "C":
							
							segments.push(new CubicBezierTo(
							pathDataArray[i+1] + ' ' + pathDataArray[i+2] +' '+
							pathDataArray[i+3] + ' ' + pathDataArray[i+4] +' '+
							pathDataArray[i+5] + ' ' + pathDataArray[i+6]))
													
							i += 6;
							break;
						
						case "s":
							
							segments.push(new CubicBezierTo(
							0 + ' ' + 0 +' '+
							pathDataArray[i+1] + ' ' + pathDataArray[i+2] +' '+
							pathDataArray[i+3] + ' ' + pathDataArray[i+4],"relative",true))
							
							i += 4;
							break;
							
						case "S":
							
							segments.push(new CubicBezierTo(
							0 + ' ' + 0 +' '+
							pathDataArray[i+1] + ' ' + pathDataArray[i+2] +' '+
							pathDataArray[i+3] + ' ' + pathDataArray[i+4],"absolute",true))
							
							
							i += 4;
							break;
						
						case "a":
							segments.push(new EllipticalArcTo(
							pathDataArray[i+1] + ' ' +
							pathDataArray[i+2] + ' ' +
							pathDataArray[i+3] + ' ' +
							pathDataArray[i+4] + ' ' +
							pathDataArray[i+5] + ' ' +
							pathDataArray[i+6] + ' ' +
							pathDataArray[i+7],"relative"));
							
							i += 7;
							break;
							
						case "A":
							segments.push(new EllipticalArcTo(
							pathDataArray[i+1] + ' ' +
							pathDataArray[i+2] + ' ' +
							pathDataArray[i+3] + ' ' +
							pathDataArray[i+4] + ' ' +
							pathDataArray[i+5] + ' ' +
							pathDataArray[i+6] + ' ' +
							pathDataArray[i+7]));
							
							i += 7;
							break;
							/*
							1 rx
							2 ry
							3 x-axis-rotation
							4 largeArcFlag
							5 sweepFlag
							6 x
							7 y
							*/		
							
						case "m":
							
							segments.push(new MoveTo(pathDataArray[i+1] + ' ' + pathDataArray[i+2],"relative"));
							i += 2;
							
							//if the next item in the array is a number 
							//assume that the items are a continued array
							//of line segments so create a new line segment 
							//for each point pair until we get to another item
							if (!isNaN(Number(pathDataArray[i+1])))
							{
								while (!isNaN(Number(pathDataArray[i+1])))
								{
									segments.push(new LineTo(pathDataArray[i+1] + ' ' + pathDataArray[i+2],"relative"));
									i+=2;
								}
							}
							
							break;
						
						case "M":
							
							segments.push(new MoveTo(pathDataArray[i+1] + ' ' + pathDataArray[i+2]));
							i += 2;
							
							//if the next item in the array is a number 
							//assume that the items are a continued array
							//of line segments so create a new line segment 
							//for each point pair until we get to another item
							if (!isNaN(Number(pathDataArray[i+1])))
							{
								while (!isNaN(Number(pathDataArray[i+1])))
								{
									segments.push(new LineTo(pathDataArray[i+1] + ' ' + pathDataArray[i+2]));
									i+=2;
								}
							}
							
							break;
								
						case "z":
						case "Z":
							segments.push(new ClosePath());
							break;
					}
				}
			
			
				invalidated = true;
				
			}
			
		} 
		
		/**
		* Converts the path data string value to an array of workable items
		**/
		private function PathDataToArray(value:String):Array{
			
			var stringToParse:String = value;
			stringToParse = stringToParse.replace(/[MmLlCcQqZzAaSsHhVvTt \- \t \n \f \s]/g,getReplaceValue);
			stringToParse = stringToParse.replace(/,,,/g,",");
			stringToParse = stringToParse.replace(/,,/g,",");
			
			return stringToParse.split(",");
			
		}
		
		/**
		* Helper function used when parsing the path data string
		**/
		private function getReplaceValue(matchedSubstring:String,itemIndex:Number,theString:String):String{	
			if (itemIndex==0){
				return matchedSubstring + ",";														
			}
			
			switch (matchedSubstring.toUpperCase()){
				case " ":
					return ",";
					break;
				case "-":
					return "," + matchedSubstring;
					break;
				default:
					return "," + matchedSubstring + ",";	
					break	
			}
						
			
		}
			
							
		private var _segments:SegmentsCollection=new SegmentsCollection();
		[Inspectable(category="General", arrayType="com.degrafa.geometry.segment.ISegment")]
		[ArrayElementType("com.degrafa.geometry.segment.ISegment")]
		/**
		* A array of segments that describe this path.
		**/
		public function get segments():Array{
			if(!_segments){_segments = new SegmentsCollection();}
			return _segments.items;
		}
		public function set segments(value:Array):void{
			if(!_segments){_segments = new SegmentsCollection();}
			_segments.items = value;
						
			//add a listener to the collection
			if(_segments && enableEvents){
				_segments.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
			
			invalidated = true;
			
		}
		
		
		/**
		* Access to the Degrafa segment collection object for this path.
		**/
		public function get segmentCollection():SegmentsCollection{
			if(!_segments){_segments = new SegmentsCollection();}
			return _segments;
		}
		
		/**
		* Principle event handler for any property changes to a 
		* geometry object or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			invalidated = true;
			dispatchEvent(event);
		}
		
		/**
		* Initialize each segment and construct an internal array of l,m,c 
		* (lineTo, moveTo, CurveTo) commands.
		**/ 
		private function buildFlashCommandStack():void{
						
			//each object hase a type l,m or c
			//keep track of the last point used during drawing
			var lastPoint:GraphicPoint = new GraphicPoint(0,0);
			
			//keep track of the first point of current 
			//path for the close command
			var firstPoint:GraphicPoint = new GraphicPoint(0,0);
						
			//store he last control point in case ShortSequence =true in 
			//which case we need to mirror the last used control point (s,S,t,T)
			var lastControlPoint:GraphicPoint=new GraphicPoint(0,0);
			
			//absolute or relative offset
			var absRelOffset:GraphicPoint = new GraphicPoint(0,0);
			
			absRelOffset.x = 0;	
   			absRelOffset.y = 0;	
									
			for (var i:int = 0;i< _segments.items.length;i++)
        	{
        		
        		var obj:Object = _segments.items[i];
        		if(ISegment(_segments.items[i]).coordinateType=="relative")
        		{
        			absRelOffset.x =lastPoint.x;
        			absRelOffset.y =lastPoint.y;
        		}
        		else
        		{
        			absRelOffset.x = 0;	
        			absRelOffset.y = 0;	
        			
        		}
        		        		
        		switch (ISegment(_segments.items[i]).segmentType)
        		{        			
        			case "LineTo":
        		
    	    			//pass the last point the abs position and the commandArray 
        				//to add the draw commands to
	        			_segments.items[i].computeSegment(lastPoint,absRelOffset,commandStack);
    	    			
    	    			lastPoint.x = absRelOffset.x+_segments.items[i].x;
	        			lastPoint.y = absRelOffset.y+_segments.items[i].y;
	        			break;
        
        			case "VerticalLineTo":
        			
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to
	        			_segments.items[i].computeSegment(lastPoint,absRelOffset,commandStack);
        				
	        			lastPoint.y = absRelOffset.y+_segments.items[i].y;
	        			
	    				break;
        
        			case "HorizontalLineTo":
        		
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to
	        			_segments.items[i].computeSegment(lastPoint,absRelOffset,commandStack);
	        			
	        			lastPoint.x = absRelOffset.x+_segments.items[i].x;
	    				break;
        
        			case "MoveTo":
        			
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to
	        			_segments.items[i].computeSegment(lastPoint,absRelOffset,commandStack);
        				
        				//reset stop points
    	    			lastPoint.x = _segments.items[i].x+absRelOffset.x;
	        			lastPoint.y = _segments.items[i].y+absRelOffset.y;
        				
        				
        				firstPoint.x=_segments.items[i].x+absRelOffset.x;
        				firstPoint.y=_segments.items[i].y+absRelOffset.y;
        				        				
        				break;
        
        			case "QuadraticBezierTo":
        			
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to, here we also need to pass the 
        				//last control point for continous bezier paths support
        				_segments.items[i].computeSegment(lastPoint,absRelOffset,lastControlPoint,commandStack);
        				
        				if(_segments.items[i].cx==0 && _segments.items[i].cy ==0
        				){
        					lastControlPoint.x = absRelOffset.x+_segments.items[i-1].cx;
	        				lastControlPoint.y = absRelOffset.y+_segments.items[i-1].cy;
        				}
        				else{
        					lastControlPoint.x = absRelOffset.x+_segments.items[i].cx;
	        				lastControlPoint.y = absRelOffset.y+_segments.items[i].cy;
	        			}
	        			       			
        				lastPoint.x = absRelOffset.x+_segments.items[i].x;
	        			lastPoint.y = absRelOffset.y+_segments.items[i].y;
	        				        			
        				break;
        
        			case "CubicBezierTo":
        			
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to, here we also need to pass the 
        				//last control point for continous bezier paths support
        				_segments.items[i].computeSegment(lastPoint,absRelOffset,lastControlPoint,commandStack);
        				
						lastControlPoint.x = absRelOffset.x+_segments.items[i].cx1;
	        			lastControlPoint.y = absRelOffset.y+_segments.items[i].cy1;
	        			
						lastPoint.x = absRelOffset.x+_segments.items[i].x;
	        			lastPoint.y = absRelOffset.y+_segments.items[i].y;
        				
        				break;
        
        			case "EllipticalArcTo":
        			
        				//pass the last point the abs position and the commandStack 
        				//to add the draw commands to
	        		    _segments.items[i].computeSegment(lastPoint,absRelOffset,commandStack);
				    			        			
	        			lastPoint.x = _segments.items[i].x+absRelOffset.x;
	        			lastPoint.y = _segments.items[i].y+absRelOffset.y;
	        			        			
        				break;
        
        			case "ClosePath":
        				//the first point used is our close point for the path
        				//this should be in all cases the move command
        				if(firstPoint)
        				{
	        				_segments.items[i].computeSegment(lastPoint,firstPoint,commandStack);
	        				
	        			}
	        			
	        			lastPoint.x = firstPoint.x;
	        			lastPoint.y = firstPoint.y;
	        			
        				break;
        				
        		}
        	}						 		
			        	
		}
		
		private var _bounds:Rectangle;
		/**
		* The tight bounds of this element as represented by a Rectangle object. 
		**/
		public function get bounds():Rectangle{
			return _bounds;	
		}
		
		/**
		* Calculates the bounds for this element. 
		**/				
		private function calcBounds():Rectangle{
			
			var boundsRect:Rectangle = new Rectangle();
			
			//union all segment bounds	
			for (var i:int = 0;i< _segments.items.length;i++){
        		
        		//note:: though we do calculate the moveTo segments 
        		//bounds (for other uses) we do not include in the 
        		//tight bounds calculations for a path.
        		switch (ISegment(_segments.items[i]).segmentType){
        			case "MoveTo":
        				break;
        			
        			default:	
        				boundsRect = boundsRect.union(_segments.items[i].bounds);
        		}
        		
        	}		
        	
        	_bounds = boundsRect;
        	
        	return _bounds;
        	
		}
		
		/**
		* @inheritDoc 
		**/
		override public function preDraw():void{
			
			//see if any segments are invalide but only if we are not already invalide
			//we do this as in the case of segments with events disabled we could not 
			//know otherwise
			if(!invalidated){
				//verify
				for (var i:int=0;i<segments.length;i++){
					if(segments[i].invalidated){
						invalidated = true;
						break;
					}
				}
			}
			
			//rebuild an array of flash commands and 
			//recalculate the bounds if required	
			if(invalidated){
				commandStack=[];
				buildFlashCommandStack();
				calcBounds();
				invalidated = false;
			}
			
		}
		
		/**
		* An Array of flash rendering commands that make up this element. 
		**/
		protected var commandStack:Array=[];		
		
		/**
		* Begins the draw phase for geometry objects. All geometry objects 
		* override this to do their specific rendering.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds. 
		**/
		override public function draw(graphics:Graphics,rc:Rectangle):void{				
		 	
		 	//re init if required
		 	preDraw();
		 	
        	//apply the fill retangle for the draw
			if(!rc){				
				super.draw(graphics,_bounds);	
			}
			else{
				super.draw(graphics,rc);
			}
			
        	var i:int=0;	
        	for (i;i<commandStack.length;i++){
        		
        		switch(commandStack[i].type){
        			
        			case "l":
        				graphics.lineTo(commandStack[i].x,commandStack[i].y);
        				break;
        		
        			case "m":
        				graphics.moveTo(commandStack[i].x,commandStack[i].y);
        				break;
        		
        			case "c":
        				graphics.curveTo(commandStack[i].cx,commandStack[i].cy,commandStack[i].x1,commandStack[i].y1);
        				break;
        		}
        	}	
        	
        	super.endDraw(graphics);
        	        	        	
		}
		
	}
}