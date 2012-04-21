/**
 * @file main.cpp
 *
 * Sample application that illustrates how to call into C++
 * from JavaScript.
 */

// Include Moblet for web applications.
#include <Wormhole/WebAppMoblet.h>

#include "StringBuilder.h"
#include <MAUtil/DataHandler.h>

// Namespaces we want to access.
using namespace MAUtil; // Class Moblet
using namespace NativeUI; // WebView widget.
using namespace Wormhole; // Class WebAppMoblet

/**
 * The application class.
 */
class MyMoblet : public WebAppMoblet
{
private:
	char* storeName;
public:
	MyMoblet()
	{
		storeName = "WhappyZap";

		// Enable message sending from JavaScript to C++.
		enableWebViewMessages();

		// Remove this line to enable the user to
		// zoom the web page. To disable zoom is one
		// way of making web pages display in a
		// reasonable degault size on devices with
		// different screen sizes.
		//getWebView()->disableZoom();

		MAHandle myStore = maOpenStore(storeName, 0);
		MAHandle myData = maCreatePlaceholder();

		if(myStore != STERR_NONEXISTENT)
		{
			int result = maReadStore(myStore, myData);

			if(result == RES_OUT_OF_MEMORY)
			{
				// This store is too large to read into memory - error
			}
			else
			{
				if(maGetDataSize(myData) > 0)
				{
					DataHandler* handler = new DataHandler(myData);

					//Username ophalen
					int userLength;

					handler->read(&userLength, 4);
					char username[userLength+1];

					handler->read(&username, userLength);
					username[userLength] = '\0';

					//Password ophalen
					int passLength;

					handler->read(&passLength, 4);
					char password[passLength+1];

					handler->read(&password, passLength);
					password[passLength] = '\0';

					//Rooster ophalen
					int roosterLength;

					handler->read(&roosterLength, 4);
					char rooster[roosterLength+1];

					handler->read(&rooster, roosterLength);
					rooster[roosterLength] = '\0';

					String url = "";

					StringBuilder sb;
					sb.append("password=");
					sb.append(password);
					sb.append("&username=");
					sb.append(username);
					sb.append("&klas=");
					sb.append(rooster);

					url = sb.toString();

					// er is wat opgeslagen
					//showPage("index.html");
					showPage("home.html?"+url);
				}
				else
				{
					showPage("home.html?action=gegevens");
				}
			}
		}
		else
		{
			myStore = maOpenStore(storeName, MAS_CREATE_IF_NECESSARY);
			showPage("home.html?action=gegevens");
		}

		// The page in the "LocalFiles" folder to
		// show when the application starts.
		//showPage("index.html");
		//callJS("testert('jahoor')");
	}

	/**
	 * This method handles messages sent from the WebView.
	 * @param webView The WebView that sent the message.
	 * @param urlData Data object that holds message content.
	 * Note that the data object will be valid only during
	 * the life-time of the call of this method, then it
	 * will be deallocated.
	 */
	void handleWebViewMessage(WebView* webView, MAHandle urlData)
	{
		// Create message object. This parses the message.
		WebViewMessage message(webView, urlData);
/*
		if (message.is("Test"))
		{
			String vari = message.getParam("vari");

			printf("%s", vari.c_str());

			const char* testChar = vari.c_str();

			String test = "";

			StringBuilder sb;
			sb.append(testChar);
			test = sb.toString();

			//callJS("testert('"+test+"')");
			showPage("home.html");
		}*/

		if(message.is("Opslaan"))
		{
			String snummer = message.getParam("snummer");
			String wachtwoord = message.getParam("wachtwoord");
			String rooster = message.getParam("rooster");

			int snummerLength = snummer.length();
			int wachtwoordLength = wachtwoord.length();
			int roosterLength = rooster.length();

			MAHandle myStore = maOpenStore(storeName, 0);
			MAHandle myData = maCreatePlaceholder();

			if(myStore != STERR_NONEXISTENT)
			{
				int size = snummerLength+wachtwoordLength+roosterLength+(4*3);

				if(maCreateData(myData, size) == RES_OK)
				{
					DataHandler* handle = new DataHandler(myData);

					handle->write(&snummerLength, 4);
					handle->write(snummer.c_str(), snummerLength);

					handle->write(&wachtwoordLength, 4);
					handle->write(wachtwoord.c_str(), wachtwoordLength);

					handle->write(&roosterLength, 4);
					handle->write(rooster.c_str(), roosterLength);

					int result = maWriteStore(myStore, myData);

					if(result > 0)
					{
						maCloseStore(myStore, 0);

						//showPage("home.html?username="+snummer+"&password="+wachtwoord+"&klas="+rooster);
						//showPage("index.html");
						callJS("opgeslagen('true', '"+snummer+"', '"+wachtwoord+"', '"+rooster+"')");
					}
					else
					{
						maCloseStore(myStore, 1);

						//showPage("home.html?error=opslaan");
						callJS("opgeslagen('false')");
					}
				}
				else
				{
					//showPage("home.html?error=opslaan");
					callJS("opgeslagen('false')");
				}
			}
			//callJS("opgeslagen('false')");
			//callJS("test('snummer: "+snummer+"')");
		}

		//callJS("test('haha')");

		// Tell the WebView that we have processed the message, so that
		// it can send the next one.
		callJS("bridge.messagehandler.processedMessage()");
	}
};

/**
 * Main function that is called when the program starts.
 * Here an instance of the MyMoblet class is created and
 * the program enters the main event loop.
 */
extern "C" int MAMain()
{
	Moblet::run(new MyMoblet());
	return 0;
}
