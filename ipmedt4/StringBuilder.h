/* 
Copyright (C) 2010 MoSync AB

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

#ifndef STRINGBUILDER_H_
#define STRINGBUILDER_H_

#include <MAUtil/Vector.h>
#include <MAUtil/String.h>
#include <MAUtil/PlaceholderPool.h>
#include <maarg.h>
using namespace MAUtil;

//StringBuilder is a very basic copy of the .NET StringBuilder class.  It allows you to incrementally build a string
//without the performance problems associated with String, where the data may have to be copied to a new array.
class StringBuilder
{
	public:
		StringBuilder(int defaultLength = 256);
		~StringBuilder();

		//Copies the text to the string builder.  The original string can be deleted
		void append(const char* text);
		void append(char* text);
		void append(String* text);
		void append(String text);

		//Formats and copies the text to the string builder.  The original string can be deleted
		void appendFormat(const char* format, ...);

		//Creates a new string with the contents of the string builder.  This is a new copy of the data
		String& toString();

		//Creates a new data obejct with the contents of the string builder.  This is a new copy of the data
		//Responsibility for this data passes to the consumer
		MAHandle toData();

		int length();

		void clear();

	private:
		int mLen; //The total length of the string, including terminator.
		Vector<char*> mStrings;
		char* mLastString;
		int mStringLength;
		int mIsDirty;
		int mCharsUsed;
		String mOutput;
		void reserveString(int len = -1);
};


#endif /* STRINGBUILDER_H_ */
