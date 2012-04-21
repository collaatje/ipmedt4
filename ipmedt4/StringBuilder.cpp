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

#include "StringBuilder.h"
#include <mastring.h>
//#include "snprintf.h"
//#include "../std.h"
#include <conprint.h>

StringBuilder::StringBuilder(int defaultLength)
			 : mLen(0),
			   mIsDirty(true)
{
	mStringLength = defaultLength;
	//Create an initial string
	reserveString();
}

StringBuilder::~StringBuilder()
{
	//Delete the data
	Vector_each(char*, itr, mStrings)
		delete [] (*itr);
}

/* Append a formatted string *
void StringBuilder::appendFormat(const char* format, ...)
{
	char* formattedText;
	va_list args;
	va_start(args, format);
	vasprintf(&formattedText, format, args);
	va_end(args);
	append(formattedText);

	delete [] formattedText;
}*/

void StringBuilder::append(const char* text)
{
	append((char*)text);
}

void StringBuilder::append(String* text)
{
	append(text->c_str());
}

void StringBuilder::append(String text)
{
	append(text.c_str());
}

void StringBuilder::append(char* text)
{
	//LOG("Appending %s", text);
	int newStrLen = strlen(text);
	char* firstChar = mLastString + mCharsUsed;

	//Determine if the current string has enough space
	if(mCharsUsed + newStrLen < mStringLength)
	{
		//copy the text
		while(*firstChar++ = *text++);
		//LOG("mLen %d + newStrLen %d = %d", mLen, newStrLen, mLen + newStrLen);
		mLen += newStrLen;
		mCharsUsed += newStrLen;

		if(mCharsUsed > mStringLength)
			maPanic(0, "Buffer overflow detected");
	}
	else
	{
		//append what we can on this string, and call again with the remainder
		//LOG("copyamount = mStringLength %d - mCharsUsed %d -1", mStringLength, mCharsUsed);
		int copyAmount = mStringLength - mCharsUsed - 1; //-1 for terminator
		for(int i = 0; i < copyAmount; i++)
			*firstChar++ = *text++;

		//LOG("mLen %d + copyAmount %d = %d", mLen, copyAmount, mLen + copyAmount);
		mLen += copyAmount;

		//Create a new string
		reserveString();

		//Call again to finish.
		append(text);
	}

	mIsDirty = true;
	//LOG("Current String '%s'", toString().c_str());
	//LOG("%d bytes added", newStrLen);
	//LOG("Now contains %d bytes", mLen);
}

int StringBuilder::length()
{
	return mLen;
}

String& StringBuilder::toString()
{
	if(mIsDirty)
	{
		//LOG("Is Dirty");
		mOutput.clear();
		//LOG("Reserving %d bytes", mLen);
		mOutput.reserve(mLen);
		Vector_each(char*, itr, mStrings)
		{
			//LOG("appending");
			//LOG("%s", *itr);
			mOutput += *itr;
		}

		mIsDirty = false;
	}
	return mOutput;
}

MAHandle StringBuilder::toData()
{
	//Copy the entire data into the data object
	MAHandle data = PlaceholderPool::alloc();
	//LOG("Creating data of %d bytes", mLen);
	if(maCreateData(data, mLen) == RES_OK)
	{
		int ctr = 0;
		Vector_each(char*, itr, mStrings)
		{
			maWriteData(data, *itr, ctr, strlen(*itr));
			ctr += strlen(*itr);
		}
	}

	return data;
}

void StringBuilder::reserveString(int len)
{
	if(len == -1)
		len = mStringLength;

	//LOG("Reserving string of %d chars", len);
	mLastString = new char[len];
	memset(mLastString, 0, len);
	mStrings.add(mLastString);
	mCharsUsed = 0;
}

void StringBuilder::clear()
{
	//Delete all the existing data
	//LOG("Freeing %d bytes", mLen);
	Vector_each(char*, itr, mStrings)
		delete [] *itr;

	mStrings.clear();
	mOutput.clear();

	//Add the default string again
	reserveString();

	mIsDirty = true;
	mLen = 0;
	//LOG("String builder has cleared");
}
