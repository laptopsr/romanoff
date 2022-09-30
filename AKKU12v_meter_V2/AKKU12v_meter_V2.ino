#if defined(ESP8266)
  #include <ESP8266WiFi.h>
  #include <ESP8266WebServer.h>
#else
  #include <WiFi.h>
  #include <WebServer.h>
#endif 

#include <WiFiClient.h>
#include <ArduinoHttpClient.h>
#include <EEPROM.h>

#define AKKU A0
String cor110               = "0";
String cor135               = "0";

int this_IP_end             = 103;
const char* device_name     = "AKKU_V2";
const char* ssid            = "Имя Вайфай";
const char* password        = "Вайфай пароль";

const char serverAddress[]  = "192.168.1.101";
String url_query            = "/esp_data.php";

float bat                   = 0.00;
float volt                  = 0.00;
String postData;
int statusCode;
int server_data             = 2;

WiFiClient wifi;
HttpClient client = HttpClient(wifi, serverAddress, 80);

unsigned long err_count = 0;
unsigned long previousMillis = 0;
unsigned long interval = 10000;
unsigned long conn_count = 0;

// Set your Static IP address
IPAddress local_IP(192, 168, 1, this_IP_end);
// Set your Gateway IP address
IPAddress gateway(192, 168, 1, 1);

IPAddress subnet(255, 255, 0, 0);
IPAddress primaryDNS(8, 8, 8, 8);   //optional
IPAddress secondaryDNS(8, 8, 4, 4); //optional

#if defined(ESP8266)
  ESP8266WebServer server(80);
#else
  WebServer server(80);
#endif

String bootstrap =
  "<meta charset='UTF-8'>"
  "<meta name='viewport' content='width=device-width, initial-scale=1.0'>"
  "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css' integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T' crossorigin='anonymous'>"
  "<script src='https://code.jquery.com/jquery-3.3.1.slim.min.js' integrity='sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo' crossorigin='anonymous'></script>"
  "<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js' integrity='sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1' crossorigin='anonymous'></script>"
  "<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js' integrity='sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM' crossorigin='anonymous'></script>";

String open_body_with_refresh =
  "<html>"
  "<header>"
    "<meta http-equiv='refresh' content='10'>"
    + bootstrap +
  "</header>"
  "<body class='container container-fluid' style='margin-top: 20px'>";

String open_body_no_refresh =
  "<html>"
  "<header>"
    + bootstrap +
  "</header>"
  "<body class='container container-fluid' style='margin-top: 20px'>";

String close_body = "</body></html>";

String style  = "<style>#w{margin-top: 20px}</style>";

void webpage() {
  getNow();
  String tempString = "";
  if(postData != "")
  {
    tempString += "<h1 class='text-success text-center' style='font-size:550%'>" + (String)volt + "V</h1>";
  }

  String servr_query = (String)statusCode + " " + server_data;
  if((String)statusCode == "200" && server_data == 1)
  {
    servr_query = "<h2 class='text-success'>OK</h2>";
  }
  if((String)statusCode != "200" && server_data == 1)
  {
    servr_query = "<h2 class='text-warning'>Попытка отправки</h2>";
  }
  if(server_data == 2)
  {
    servr_query = "<h2 class='text-warning'>Отключено</h2>";
  }

  server.send(200, "text/html",
    open_body_with_refresh + "" + style +
      tempString +    
      "<hr>"
      "Связь с сервером: "+ (String)servr_query +""
      "<hr>"
      "<p>"
      "Пауза между отсылками (в секундах): <b>"+(String)EEPROM.read(1)+"</b><br>"
      "Показания A0 для 11.00 вольт: <b>" + (String)cor110 + "</b><br>"
      "Показания A0 для 13.50 вольт: <b>" + (String)cor135 + "</b><br>"
      "URL для JSON: <a href='getBat'>getBat</a><br>"
      "A0: <b>" + (String)bat + "</b>"
      "<hr>"
      "URL приема данных: <b>http://" + (String)serverAddress + "" + url_query + "</b><br>"
      "POST data: <b>" + (String)postData + "</b>"
      "</p>"

      "<p>"
      "<h4>Пример PHP кода для " + url_query + ":</h4>"
      "<textarea class='form-control' rows='5'><?php\n"
      "$device_name   = $_POST['device_name']??'';\n"
      "$voltage       = $_POST['voltage']??'';\n"
      "mail('Ваш@емайл', 'Данные от аккумулятора', 'Аккумулятор сейчас: ' .$voltage);\n"
      "?></textarea>"
      "</p>"
      "<a href='functions' class='btn btn-primary btn-block'>Настройки</a>"
    + close_body
  );
}

void response(){

  if(server.hasArg("esp") && (server.arg("esp").length()>0))
  {
    if(server.arg("esp") == "ESP RESTART")
    {
      ESP.restart();
    }
    server.send(400, "text/html", "<html><body><a href='/' class='btn btn-primary btn-block'>Home..</a></body></html>");
  } else if(server.hasArg("set"))
  {
    int set1    = server.arg("set1").toInt();
    EEPROM.write(1, set1);

    int set3    = server.arg("set3").toInt();
    EEPROM.write(3, set3);

    /* cor110 */
    int set110    = server.arg("set110").toInt();
    float s110n1  = set110 / 100;
    float s110n2  = set110 / 10;
    float s110n3  = set110 * 100;
    int d110n1    = 100 * s110n1;
    int d110n2    = (100 * s110n2) - (d110n1 * 10);
    int d110n3    = s110n3 - ((d110n1*100) + (d110n2 * 10));

    EEPROM.write(41, d110n1/100);
    EEPROM.write(42, d110n2/100);
    EEPROM.write(43, d110n3/100);

    /* cor135 */
    int set135    = server.arg("set135").toInt();
    float s135n1  = set135 / 100;
    float s135n2  = set135 / 10;
    float s135n3  = set135 * 100;
    int d135n1    = 100 * s135n1;
    int d135n2    = (100 * s135n2) - (d135n1 * 10);
    int d135n3    = s135n3 - ((d135n1*100) + (d135n2 * 10));

    EEPROM.write(51, d135n1/100);
    EEPROM.write(52, d135n2/100);
    EEPROM.write(53, d135n3/100);

    /*
    float st    = server.arg("set2").toFloat();
    float sn1   = st / 100;
    float sn2    = st / 10;
    float sn3    = st * 100;
    int d1      = 100 * sn1;
    int d2      = (100 * sn2) - (d1 * 10);
    int d3      = sn3 - ((d1*100) + (d2 * 10));

    EEPROM.write(2, d1);
    EEPROM.write(22, d2);
    EEPROM.write(222, d3);
    resistorCorrection  = (String)EEPROM.read(2) + "." + (String)EEPROM.read(22) + "" + (String)EEPROM.read(222);
    */

    EEPROM.commit(); // For save data

    cor110      = (String)EEPROM.read(41) + "" + (String)EEPROM.read(42) + "" + (String)EEPROM.read(43);
    cor135      = (String)EEPROM.read(51) + "" + (String)EEPROM.read(52) + "" + (String)EEPROM.read(53);
    server_data = EEPROM.read(3);

    server.send(400, "text/html", "<html><meta http-equiv='refresh' content='2;url=/' /><body><h1><a href='/' class='btn btn-primary btn-block'>Home..</a></h1></body></html>");

  } else {
    server.send(400, "text/html", "<html><body><h1>HTTP Error 400</h1><p>Bad request. Please enter a value.</p></body></html>");
  }
}

void setup(void) {
  Serial.begin(115200);

  EEPROM.begin(4096);

  if(EEPROM.read(41) != 255 && EEPROM.read(42) != 255 && EEPROM.read(43) != 255)
  {
    cor110 = (String)EEPROM.read(41) + "" + (String)EEPROM.read(42) + "" + (String)EEPROM.read(43);
  }
  if(EEPROM.read(51) != 255 && EEPROM.read(52) != 255 && EEPROM.read(53) != 255)
  {
    cor135 = (String)EEPROM.read(51) + "" + (String)EEPROM.read(52) + "" + (String)EEPROM.read(53);
  }
  if(EEPROM.read(3) != 255)
  {
    server_data = EEPROM.read(3);
  }
  delay(1000);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.println("");

  // Configures static IP address
  if (!WiFi.config(local_IP, gateway, subnet, primaryDNS, secondaryDNS)) {
    Serial.println("STA Failed to configure");
  }
  
  // Wait for connection
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to ");
  Serial.println(ssid);
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());

  server.on("/",HTTP_GET, webpage);
  server.on("/",HTTP_POST,response);

  server.on("/getBat", []() {
    getNow();
    server.send(200, "text/plain", "{\"voltage_now\":" + (String)volt + "}");
  });

  server.on("/functions", []()
  {
    server.send(200, "text/html", 
      open_body_no_refresh +
        "<a href='/' class='btn btn-primary'>Главная страница</a>"
        "<hr>"
        "<form action='/' name='frm' method='post'>"
          "<input type='hidden' name='set'>"
          "<label>Пауза между отсылками на сервер(в секундах)</label>"
          "<input type='number' class='form-control' name='set1' value='" + (String)EEPROM.read(1) + "' step='any' placeholder='20'>"
          "<label>Отправлять данные на север?</label>"
          "<select class='form-control' name='set3'>"
          "<option value='2' "+ (server_data == 2 ? "selected" : "") +">Нет</option>"
          "<option value='1' "+ (server_data == 1 ? "selected" : "") +">Да</option>"
          "</select>"
          "<hr>"
          "<h3>Корректировка вольтметра:</h3>"

          "<label>Показания A0 для 11.00 вольт</label>"
          "<input type='number' class='form-control' name='set110' value='" + cor110.toInt() + "' step='any' placeholder='Например: 666'>"

          "<label>Показания A0 для 13.50 вольт</label>"
          "<input type='number' class='form-control' name='set135' value='" + cor135.toInt() + "' step='any' placeholder='Например: 760'>"
          "<br>"

          "<input type='submit' class='btn btn-primary btn-block' value='Сохранить'>"
        "</form>"
        "<hr>"
        "<form action='/' name='frm' method='post'>"
          "<input type='submit' name='esp' class='btn btn-primary btn-block' value='ESP RESTART'>"
        "</form>"
      + close_body
    );   
  });

  server.begin();
  Serial.println("HTTP server started");
}

void loop(void) {

  server.handleClient();
  
  // <-- Check connection
  unsigned long currentMillis = millis();
  if (currentMillis - previousMillis >= EEPROM.read(1)*1000)
  {
    previousMillis = currentMillis;
    getNow();

    if(postData != "" && server_data == 1)
    {
      Serial.println("making POST request");    
      client.beginRequest();
      client.post(url_query);
      client.sendHeader("Content-Type", "application/x-www-form-urlencoded");
      client.sendHeader("Content-Length", postData.length());
      client.sendHeader("X-Custom-Header", "custom-header-value");
      client.beginBody();
      client.print(postData);
      client.endRequest();

      // read the status code and body of the response
      statusCode = client.responseStatusCode();
      String response = client.responseBody();
    
      Serial.print("Status code: ");
      Serial.println(statusCode);
      Serial.print("Response: ");
      Serial.println(response);
    }
  }
  // Check connection -->
}

void getNow()
{
    bat       = analogRead(AKKU);

    //
    float vmin    = 11;
    float vmax    = 13.5;
    float vero    = (vmax-vmin)*10;
    
    float ero     = (cor135.toFloat()-cor110.toFloat())/vero;
    float sero    = (vmax-vmin)/vero;
    
    float s       = cor110.toFloat();
    float v       = 11;
    float before  = s-ero;
    float next    = s+ero;
    for (int x = 0; x < vero; x++)
    {
      next   = s+ero;
    
      if(bat >= before && bat <= next)
      {
        volt = bat * (v/s);
        break;
      }
    
      s += ero;
      v += sero;
      before = s-ero;
    }
    //

    Serial.println((String)v);

    postData    = "device_name="+(String)device_name+"&a0="+bat+"&voltage="+volt+"";
}
