import streamlit as st
import pandas as pd
import numpy as np
import joblib
from datetime import datetime
import calendar
from sklearn.preprocessing import LabelEncoder

# Konfigurasi halaman
st.set_page_config(
    page_title="Prediksi Musim Buah - Gudang Buah",
    layout="wide"
)

# Kamus data musiman
SEASONAL_DATA = {
    (1, 2, 3): ['Durian', 'Rambutan', 'Alpukat', 'Manggis', 'Sawo', 'Kedondong', 
                'Salak', 'Jambu Biji', 'Jeruk Nipis', 'Duku', 'Jeruk Bali', 'Sirsak'],
    (4, 5, 6): ['Kesemek', 'Jeruk Manis', 'Salak', 'Jeruk Nipis', 'Duku', 'Jeruk Bali',
                'Kedondong', 'Jambu Biji', 'Jambu Air'],
    (7, 8, 9): ['Kesemek', 'Jeruk Manis', 'Belimbing', 'Melon', 'Jambu Mete',
                'Jambu Bol', 'Mangga'],
    (10, 11, 12): ['Durian', 'Manggis', 'Rambutan', 'Alpukat', 'Sawo', 'Jeruk Bali']
}

def check_seasonality(month, fruit_name):
    """Check if a fruit is in season for a given month"""
    for months, fruits in SEASONAL_DATA.items():
        if month in months and fruit_name in fruits:
            return 1
    return 0

def validate_csv_format(df):
    """Validate the uploaded CSV format"""
    required_columns = ['id', 'nama_buah', 'kategori', 'satuan', 
                       'stok_awal', 'stok_masuk', 'stok_keluar', 
                       'stok_akhir', 'tanggal_masuk']
    
    # Check if all required columns exist (case-insensitive)
    df.columns = df.columns.str.lower()
    if not all(col in df.columns for col in required_columns):
        return False
    
    # Check data types
    try:
        df['tanggal_masuk'] = pd.to_datetime(df['tanggal_masuk'])
        df[['stok_awal', 'stok_masuk', 'stok_keluar', 'stok_akhir']] = \
            df[['stok_awal', 'stok_masuk', 'stok_keluar', 'stok_akhir']].astype(float)
        return True
    except:
        return False

def load_data(uploaded_file):
    """Load and preprocess the uploaded CSV file"""
    try:
        df = pd.read_csv(uploaded_file)
        
        if not validate_csv_format(df):
            st.error("Format CSV tidak sesuai. Pastikan semua kolom yang diperlukan ada.")
            return None
            
        # Standardize column names
        df.columns = df.columns.str.lower()
        
        # Convert dates to datetime
        df['tanggal_masuk'] = pd.to_datetime(df['tanggal_masuk'])
        
        return df
    except Exception as e:
        st.error(f"Error saat membaca file: {str(e)}")
        return None

def predict_optimal_stock(nama_buah, satuan, month, historical_data):
    """Predict optimal stock levels using the trained model"""
    try:
        # Load the saved model and label encoder
        model = joblib.load('ai/stock_model2.joblib')
        le_satuan = joblib.load('ai/unit_encoder2.joblib')
        
        # Filter data for the specific fruit and unit
        fruit_data = historical_data[
            (historical_data['nama_buah'] == nama_buah) & 
            (historical_data['satuan'] == satuan)
        ].copy()
        
        if len(fruit_data) == 0:
            return None, {'error': 'Tidak ada data historis'}
        
        # Calculate features
        avg_stok_masuk = fruit_data['stok_masuk'].mean()
        std_stok_masuk = fruit_data['stok_masuk'].std()
        avg_stok_keluar = fruit_data['stok_keluar'].mean()
        std_stok_keluar = fruit_data['stok_keluar'].std()
        
        # Get seasonal status
        is_seasonal = check_seasonality(month, nama_buah)
        
        # Calculate trend
        last_3_months = historical_data['tanggal_masuk'].max() - pd.DateOffset(months=3)
        recent_data = fruit_data[fruit_data['tanggal_masuk'] >= last_3_months]
        
        if len(recent_data) > 1:
            trend = (recent_data['stok_keluar'].iloc[-1] - recent_data['stok_keluar'].iloc[0]) / len(recent_data)
        else:
            trend = 0
        
        # Get encoded unit
        satuan_encoded = le_satuan.transform([satuan])[0]
        
        # Create feature array
        features = np.array([
            [avg_stok_masuk, std_stok_masuk, avg_stok_keluar, 
             std_stok_keluar, is_seasonal, satuan_encoded, trend]
        ])
        
        # Make prediction
        prediction = model.predict(features)[0]
        
        # Prepare additional info
        info = {
            'is_seasonal': bool(is_seasonal),
            'trend': trend,
            'avg_stok_masuk': avg_stok_masuk,
            'avg_stok_keluar': avg_stok_keluar,
            'satuan': satuan
        }
        
        return np.ceil(prediction), info
    except Exception as e:
        st.error(f"Error dalam prediksi: {str(e)}")
        return None, {'error': str(e)}

def show_predictions(month, month_name, historical_data):
    """Display predictions for the selected month"""
    st.header(f"Prediksi Stok Bulan {month_name}")
    
    # Get unique combinations of fruit names and units
    fruit_units = historical_data[['nama_buah', 'satuan']].drop_duplicates()
    
    # Create two columns
    col1, col2 = st.columns(2)
    
    with col1:
        st.subheader("Buah Musiman")
        for _, row in fruit_units.iterrows():
            if check_seasonality(month, row['nama_buah']):
                pred, info = predict_optimal_stock(
                    row['nama_buah'], 
                    row['satuan'], 
                    month, 
                    historical_data
                )
                if pred is not None:
                    with st.expander(f"{row['nama_buah']} ({row['satuan']})"):
                        st.info(f"Rekomendasi stok: {int(pred)} {row['satuan']}")
                        st.write(f"Trend: {'Naik' if info['trend'] > 0 else 'Turun' if info['trend'] < 0 else 'Stabil'}")
                        st.write(f"Rata-rata stok masuk: {int(info['avg_stok_masuk'])} {row['satuan']}")
                        st.write(f"Rata-rata stok keluar: {int(info['avg_stok_keluar'])} {row['satuan']}")
    
    with col2:
        st.subheader("Buah Non-Musiman")
        for _, row in fruit_units.iterrows():
            if not check_seasonality(month, row['nama_buah']):
                pred, info = predict_optimal_stock(
                    row['nama_buah'], 
                    row['satuan'], 
                    month, 
                    historical_data
                )
                if pred is not None:
                    with st.expander(f"{row['nama_buah']} ({row['satuan']})"):
                        st.success(f"Rekomendasi stok: {int(pred)} {row['satuan']}")
                        st.write(f"Trend: {'Naik' if info['trend'] > 0 else 'Turun' if info['trend'] < 0 else 'Stabil'}")
                        st.write(f"Rata-rata stok masuk: {int(info['avg_stok_masuk'])} {row['satuan']}")
                        st.write(f"Rata-rata stok keluar: {int(info['avg_stok_keluar'])} {row['satuan']}")

def main():
    st.title('Website Sistem Prediksi Rekomendasi Stok Musim Buah')
    st.write("""
        Selamat datang di **Website Sistem Prediksi Rekomendasi Stok Musim Buah**. Website ini dirancang untuk mengunggah file CSV dari tabel data transaksi dan 
        mendapatkan rekomendasi stok buah pada bulan ini dan bulan depan.
        
        Unggah File CSV dari tabel data transaksi yang kamu unduh pada halaman tabel data untuk melihat rekomendasi stok buah bulan ini dan bulan depan.
    """)

    # File uploader
    uploaded_file = st.file_uploader("Upload file CSV transaksi", type=['csv'])
    
    if uploaded_file is not None:
        historical_data = load_data(uploaded_file)
        
        if historical_data is not None:
            # Get current and next month
            current_month = datetime.now().month
            next_month = current_month % 12 + 1
            
            # Get month names
            current_month_name = calendar.month_name[current_month]
            next_month_name = calendar.month_name[next_month]

            # Month selection
            selected_month = st.selectbox(
                "Pilih Bulan Prediksi",
                ["Bulan Ini", "Bulan Depan"],
                index=0
            )

            # Show predictions based on selection
            if selected_month == "Bulan Ini":
                show_predictions(current_month, current_month_name, historical_data)
            else:
                show_predictions(next_month, next_month_name, historical_data)

            # Additional information
            st.markdown("---")
            st.markdown("""
            ### Catatan:
            - Prediksi mempertimbangkan data historis 3 bulan terakhir
            - Faktor musiman meningkatkan rekomendasi stok hingga 50%
            - Tren penjualan positif meningkatkan rekomendasi stok sebesar 20%
            - Tren penjualan negatif menurunkan rekomendasi stok sebesar 20%
            """)

            # Data preview with formatted date
            st.markdown("---")
            st.subheader("Preview Data Transaksi")
            # Create a copy of the dataframe to avoid modifying the original
            display_df = historical_data.copy()
            # Convert datetime to date for display
            display_df['tanggal_masuk'] = display_df['tanggal_masuk'].dt.date
            st.dataframe(display_df)

if __name__ == "__main__":
    main()
