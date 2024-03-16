using System;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;
using System.Windows;
using System.Windows.Threading;

namespace DuyuruUygulamasi
{
    public partial class MainWindow : Window
    {
        private string dosyaAdi = "duyurular.txt";
        private string ftpSunucu = "ftp://188.132.198.82/public_html/";
        private string ftpKullaniciAdi = "palmiyel";
        private string ftpSifre = "97lPj7]Ql[jU2Y";
        private bool ilkCalistirma = true;
        private bool pencereGizli = true;

        public MainWindow()
        {
            InitializeComponent();
            Thread thread = new Thread(new ThreadStart(MetniKontrolEt));
            thread.IsBackground = true;
            thread.Start();

            // Tarih ve saat bilgilerini güncelle
            TarihSaatGuncelle();
        }

        private void MetniKontrolEt()
        {
            while (true)
            {
                try
                {
                    FtpWebRequest ftpRequest = (FtpWebRequest)WebRequest.Create(ftpSunucu + dosyaAdi);
                    ftpRequest.Credentials = new NetworkCredential(ftpKullaniciAdi, ftpSifre);
                    ftpRequest.Method = WebRequestMethods.Ftp.DownloadFile;

                    using (FtpWebResponse ftpResponse = (FtpWebResponse)ftpRequest.GetResponse())
                    using (Stream ftpStream = ftpResponse.GetResponseStream())
                    using (StreamReader reader = new StreamReader(ftpStream))
                    {
                        string[] satirlar = reader.ReadToEnd().Split('\n');
                        if (satirlar.Length > 0 && !string.IsNullOrWhiteSpace(satirlar[0]))
                        {
                            for (int i = 0; i < satirlar.Length; i++)
                            {
                                string satir = satirlar[i].Trim();
                                string[] parcalar = satir.Split('$');
                                if (parcalar.Length == 2)
                                {
                                    string duyuruMetni = parcalar[0];
                                    int sure;
                                    if (int.TryParse(parcalar[1], out sure))
                                    {
                                        Dispatcher.Invoke(() =>
                                        {
                                            DuyuruMetni.Text = duyuruMetni;
                                            DuyuruMetni.Visibility = Visibility.Visible;
                                            pencereGizli = false;
                                            this.Show();
                                            this.Activate();
                                        });

                                        Thread.Sleep(sure * 1000);

                                        Dispatcher.Invoke(() =>
                                        {
                                            DuyuruMetni.Visibility = Visibility.Hidden;
                                            pencereGizli = true;
                                            this.Hide();
                                        });

                                        SilSatir(ftpSunucu + dosyaAdi, i);
                                    }
                                }
                            }
                        }
                        else
                        {
                            Dispatcher.Invoke(() =>
                            {
                                this.Hide();
                                pencereGizli = true;
                            });
                        }
                    }

                    ilkCalistirma = false;
                }
                catch (Exception ex)
                {
                    MessageBox.Show("Hata: " + ex.Message);
                }

                Thread.Sleep(1000); // 1 saniye beklet
            }
        }

        private void SilSatir(string ftpDosyaAdresi, int satirIndex)
        {
            try
            {
                FtpWebRequest ftpRequest = (FtpWebRequest)WebRequest.Create(ftpDosyaAdresi);
                ftpRequest.Credentials = new NetworkCredential(ftpKullaniciAdi, ftpSifre);
                ftpRequest.Method = WebRequestMethods.Ftp.DownloadFile;

                using (FtpWebResponse ftpResponse = (FtpWebResponse)ftpRequest.GetResponse())
                using (Stream ftpStream = ftpResponse.GetResponseStream())
                using (StreamReader reader = new StreamReader(ftpStream))
                {
                    string[] satirlar = reader.ReadToEnd().Split('\n');
                    StringBuilder yeniIcerik = new StringBuilder();
                    for (int i = 0; i < satirlar.Length; i++)
                    {
                        if (i != satirIndex)
                        {
                            yeniIcerik.AppendLine(satirlar[i]);
                        }
                    }

                    FtpWebRequest ftpWriteRequest = (FtpWebRequest)WebRequest.Create(ftpDosyaAdresi);
                    ftpWriteRequest.Credentials = new NetworkCredential(ftpKullaniciAdi, ftpSifre);
                    ftpWriteRequest.Method = WebRequestMethods.Ftp.UploadFile;
                    byte[] byteArray = Encoding.UTF8.GetBytes(yeniIcerik.ToString());
                    ftpWriteRequest.ContentLength = byteArray.Length;
                    using (Stream requestStream = ftpWriteRequest.GetRequestStream())
                    {
                        requestStream.Write(byteArray, 0, byteArray.Length);
                    }
                    FtpWebResponse response = (FtpWebResponse)ftpWriteRequest.GetResponse();
                    response.Close();
                }

                Dispatcher.Invoke(() =>
                {
                    if (pencereGizli)
                    {
                        this.Hide();
                    }
                });

                if (!pencereGizli)
                {
                    Dispatcher.Invoke(() =>
                    {
                        this.Show();
                        pencereGizli = true;
                    });
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Hata: " + ex.Message);
            }
        }

        private void TarihSaatGuncelle()
        {
            DispatcherTimer timer = new DispatcherTimer();
            timer.Tick += Timer_Tick;
            timer.Interval = TimeSpan.FromSeconds(1);
            timer.Start();
        }

        private void Timer_Tick(object sender, EventArgs e)
        {
            TarihTextBlock.Text = DateTime.Now.ToString("dd/MM/yyyy");
            SaatTextBlock.Text = DateTime.Now.ToString("HH:mm:ss");
        }
    }
}
